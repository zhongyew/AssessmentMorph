<script setup>
import { ref, computed, onMounted, watch } from '../../node_modules/vue'
import axios from 'axios'


const pageState = ref(0) 
const inputText = ref('')
const correctedText = ref('')
const loading = ref(false)
const userTokens = ref(3);
const projects = [
  { id: 'grammar', title: 'A Grammar Correction Tool', icon: '✍️', desc: 'A basic grammar correction tool that use a free languageTool API' },
  
]
// Control the window
const showTopUpModal = ref(false)
const highlightedOriginal = ref('');
// Define the package
const packages = [
  { id: 1, tokens: 50, price: 25, label: 'Starter Pack' },
  { id: 2, tokens: 100, price: 45, label: 'Pro Pack' },
  { id: 3, tokens: 'Unlimited', price: 200, label: 'Enterprise' }
]

// Top up 
const handleTopUp = (pkg) => {
  if (pkg.tokens === 'Unlimited') {
    userTokens.value = '∞' // Infinity
  } else {
    
    const current = userTokens.value === '∞' ? 0 : Number(userTokens.value)
    userTokens.value = current + pkg.tokens
  }
  alert(`Top up Successful! You purchased: ${pkg.label}`)
  showTopUpModal.value = false 
}

const history = ref([])
const fetchHistory = async () => {
  const res = await axios.get('http://127.0.0.1:8000/api/history');
  history.value = res.data;
}
// Initialized History
onMounted(() => {
  const saved = localStorage.getItem('grammar_history')
  if (saved) history.value = JSON.parse(saved)
})

// Update Local Storage
watch(history, (newVal) => {
  localStorage.setItem('grammar_history', JSON.stringify(newVal))
}, { deep: true })

// Favourite Function
const toggleFavorite = async () => {
  if (!correctedText.value || !highlightedOriginal.value) return;

  const tokensNeeded = Math.ceil(inputText.value.length / 100);

  try {
    const response = await axios.post('http://127.0.0.1:8000/api/history', {
      
      input_text: highlightedOriginal.value, 
      output_result: correctedText.value,
      tokens_used: tokensNeeded,
      
    });

    if (response.data.success) {
      await fetchHistory();
      alert("Saved with highlights!");
    }
  } catch (error) {
    console.error("Save failed", error);
  }
};
// Delete Function
const removeHistory = async (id) => {
  await axios.delete(`http://127.0.0.1:8000/api/history/${id}`);
  history.value = history.value.filter(item => item.id !== id);
}

onMounted(fetchHistory);

// --- Page ---
const handleStart = () => pageState.value = 1
const enterProject = (id) => { if (id === 'grammar') pageState.value = 2 }

// --- API Logic ---
const handleCheck = async () => {
  // 1. Verify the space
  if (!inputText.value.trim()) return;

  // 2. Token required calculating
  const tokensNeeded = Math.ceil(inputText.value.length / 100);

  // 3. Inifinity token can skip the verify
  if (userTokens.value !== '∞' && userTokens.value < tokensNeeded) {
    alert(`Insufficient Tokens! Requires ${tokensNeeded} tokens, but you only have ${userTokens.value}.`);
    showTopUpModal.value = true; 
    return;
  }

  loading.value = true;
  correctedText.value = ""; 
  highlightedOriginal.value = ""; 

  try {
    const response = await axios.post('http://127.0.0.1:8000/api/check', {
      text: inputText.value
    });
    
    if (response.data.success) {
      //  Correct the text
      correctedText.value = response.data.corrected;
      
      
      let text = inputText.value;
      const matches = response.data.matches || [];
      
      [...matches].sort((a, b) => b.offset - a.offset).forEach(match => {
        const wrongWord = text.substring(match.offset, match.offset + match.length);
        // Highligh the wrong word
        const highlightHtml = `<span class="highlight-err" title="${match.message}">${wrongWord}</span>`;
        text = text.substring(0, match.offset) + highlightHtml + text.substring(match.offset + match.length);
      });
      highlightedOriginal.value = text;

      // Deduct the token needed
      if (userTokens.value !== '∞') {
        userTokens.value -= tokensNeeded;
      }
    } else {
      alert("Backend Error: " + (response.data.error || "Unknown issue"));
    }
  } catch (error) {
    const errorMsg = error.response?.data?.error || error.message;
    alert("Connection Failed: " + errorMsg);
  } finally {
    loading.value = false;
  }
}
const isEnglish = computed(() => {
  if (!inputText.value.trim()) return true;


  const nonEnglishRegex = /[^\x00-\xff]/; 
  
  // Return false when nonEnglish is detected
  return !nonEnglishRegex.test(inputText.value);
});

const canSubmit = computed(() => {
  return inputText.value.trim().length > 0 && isEnglish.value && !loading.value;
});
</script>


<template>
  <div class="hero-container">
    <div class="token-status-bar">
      <div class="token-chip">
        <span class="label">Balance:</span>
        <span class="value">💎 {{ userTokens }}</span>
        <button class="topup-trigger" @click="showTopUpModal = true" title="Top Up">+</button>
      </div>
    </div>

    <Transition name="fade">
      <div v-if="showTopUpModal" class="modal-overlay" @click.self="showTopUpModal = false">
        <div class="topup-modal">
          <h2 class="modal-title">Top Up Tokens</h2>
          <p class="modal-subtitle">Select a package to continue your journey</p>
          <div class="package-grid">
            <div v-for="pkg in packages" :key="pkg.id" class="package-card" @click="handleTopUp(pkg)">
              <div class="pkg-label">{{ pkg.label }}</div>
              <div class="pkg-tokens">
                <span>{{ pkg.tokens === 'Unlimited' ? '∞' : pkg.tokens }}</span>
                <small>Tokens</small>
              </div>
              <div class="pkg-price">${{ pkg.price }}</div>
              <button class="buy-btn">Select</button>
            </div>
          </div>
          <button class="close-modal-btn" @click="showTopUpModal = false">Cancel</button>
        </div>
      </div>
    </Transition>

    <div class="glow"></div>

    <Transition name="fade-slide" mode="out-in">
      <div v-if="pageState === 0" key="welcome" class="welcome-section">
        <h1 class="title">Yap's Path Way</h1>
        <div class="glass-card">
          <button class="cool-button" @click="handleStart">START EXPLORING</button>
          <div class="status-bar"><span>Status: Online</span></div>
        </div>
      </div>

      <div v-else-if="pageState === 1" key="grid" class="content-section">
        <h2 class="section-title">My Projects</h2>
        <div class="grid">
          <div v-for="item in projects" :key="item.id" class="project-card" @click="enterProject(item.id)">
            <span class="icon">{{ item.icon }}</span>
            <h3>{{ item.title }}</h3>
            <p>{{ item.desc }}</p>
          </div>
        </div>
        <button class="back-btn" @click="pageState = 0">Back</button>
      </div>

      <div v-else-if="pageState === 2" key="checker" class="checker-page">
        <h2 class="section-title">Grammar Correction</h2>
        
        <div class="checker-container">
          <textarea 
            v-model="inputText" 
            placeholder="Enter English paragraph here..."
            :class="{ 'input-error': !isEnglish }"
          ></textarea>

          <div v-if="!isEnglish" class="error-msg">
            ⚠️ Only English characters are supported.
          </div>
<div class="input-stats">
  <span class="char-count">Characters: {{ inputText.length }}</span>
  <span class="token-estimate" :class="{ 'text-warning': userTokens !== '∞' && userTokens < Math.ceil(inputText.length / 100) }">
    Estimated Cost: 💎 {{ Math.ceil(inputText.length / 100) }} Token(s)
  </span>
</div>
          <div class="controls">
            <button class="cool-button" @click="handleCheck" :disabled="!canSubmit">
              {{ loading ? 'Analyzing...' : 'Correction' }}
            </button>
            <button class="back-btn" @click="pageState = 1" style="margin:0">Back</button>
          </div>

          <div v-if="correctedText" class="result-box">
            <div class="result-header">
              <h4>Correction Analysis:</h4>
              <button class="fav-btn" @click="toggleFavorite">⭐ Favourite</button>
            </div>
            <p class="diff-view"><strong>Original:</strong> <span v-html="highlightedOriginal"></span></p>
            <p class="diff-view"><strong>Corrected:</strong> <span class="text-success">{{ correctedText }}</span></p>
          </div>

          <div v-if="history.length > 0" class="history-section">
            <div class="history-header">
               <h3>Recall / History</h3>
               <span class="count">{{ history.length }} record(s)</span>
            </div>
            
            <div class="history-list">
              <div v-for="item in history" :key="item.id" class="history-item">
                <div class="history-content">
                  <div class="history-meta">
                    <small class="time">{{ new Date(item.created_at).toLocaleString() }}</small>
                    <div class="billing-info">
                      <span class="token-tag">💎 {{ item.tokens_used }} Token</span>
                     
                    </div>
                  </div>
                  <p><strong>Original:</strong> <span v-html="item.input_text"></span></p>
                  <p><strong>Corrected:</strong> {{ item.output_result }}</p>
                </div>
                <button class="delete-btn" @click="removeHistory(item.id)">🗑️</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.hero-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 90vh;
  color: #ffffff;
  position: relative;
  overflow-y: auto;
  padding: 20px;
}

.title {
  font-size: 4rem;
  background: linear-gradient(135deg, #646cff 0%, #42b883 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  margin-bottom: 0.5rem;
}

.content-section, .checker-page {
  width: 100%;
  max-width: 800px;
  display: flex;
  flex-direction: column;
  align-items: center;
  z-index: 10;
}

.section-title { font-size: 2.5rem; margin-bottom: 2rem; color: #42b883; }

.grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  width: 100%;
}

.checker-container {
  width: 100%;
  background: rgba(255, 255, 255, 0.05);
  padding: 2rem;
  border-radius: 20px;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

textarea {
  width: 100%;
  height: 120px;
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid #444;
  color: white;
  padding: 15px;
  border-radius: 12px;
  margin-bottom: 20px;
  font-family: inherit;
  resize: none;
}

.controls { display: flex; gap: 15px; justify-content: center; margin-bottom: 20px; }

.result-box {
  background: rgba(66, 184, 131, 0.1);
  padding: 1.5rem;
  border-radius: 12px;
  border-left: 4px solid #42b883;
  text-align: left;
  margin-bottom: 30px;
}
.result-box p {
  white-space: pre-wrap;   
  word-wrap: break-word;   
  line-height: 1.6;       
}

.result-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }

.fav-btn {
  background: rgba(255, 215, 0, 0.1);
  border: 1px solid gold;
  color: gold;
  padding: 5px 15px;
  border-radius: 20px;
  cursor: pointer;
  font-size: 0.85rem;
  transition: 0.3s;
}
.fav-btn:hover { background: gold; color: black; }
:deep(.highlight-err) {
  background-color: rgba(255, 107, 107, 0.2);
  border-bottom: 2px dashed #ff6b6b;
  cursor: help;
  padding: 0 2px;
}
:deep(.history-content .highlight-err) {
  background-color: rgba(255, 107, 107, 0.15);
  border-bottom: 1px dashed #ff4757;
  color: inherit;
}
.input-error {
  border: 1px solid #ff4757 !important;
  box-shadow: 0 0 10px rgba(255, 71, 87, 0.2);
}

.error-msg {
  color: #ff4757;
  font-size: 0.8rem;
  margin-top: 5px;
  text-align: left;
  animation: shake 0.3s;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-5px); }
  75% { transform: translateX(5px); }
}


.cool-button:disabled {
  background: #333;
  cursor: not-allowed;
  opacity: 0.6;
}

.text-success {
  color: #42b883;
  font-weight: 500;
}

.diff-view {
  margin-bottom: 15px;
  line-height: 1.6;
}
.history-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  padding-bottom: 5px;
}

.time { color: #888; font-size: 0.75rem; }

.billing-info { display: flex; gap: 12px; font-size: 0.85rem; }

.token-tag {
  color: #42b883;
  background: rgba(66, 184, 131, 0.1);
  padding: 2px 8px;
  border-radius: 4px;
}

.history-section {
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding-top: 20px;
  text-align: left;
}
.history-content p {
  margin: 8px 0;
  font-size: 0.9rem;
  line-height: 1.5;
  max-height: 100px;        
  overflow-y: auto;         
  padding-right: 5px;
  word-break: break-word;   
  white-space: pre-wrap;    
}

/* 自定义滚动条样式，使其更美观 */
.history-content p::-webkit-scrollbar {
  width: 4px;
}
.history-content p::-webkit-scrollbar-thumb {
  background: rgba(66, 184, 131, 0.3);
  border-radius: 10px;
}
.history-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
.history-header h3 { color: #42b883; font-size: 1.2rem; }
.count { color: #888; font-size: 0.8rem; }

.history-item {
  background: rgba(255, 255, 255, 0.03);
  padding: 15px;
  border-radius: 12px;
  margin-bottom: 10px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.history-content p { margin: 4px 0; font-size: 0.9rem; line-height: 1.4; }
.history-content small { color: #555; font-size: 0.7rem; }
.token-status-bar {
  position: fixed;   
  top: 30px;          
  right: 40px;        
  z-index: 1000;      
}

.token-chip {
  background: rgba(0, 0, 0, 0.6);         
  backdrop-filter: blur(12px);            
  padding: 8px 18px;                     
  border-radius: 50px;
  border: 1px solid rgba(66, 184, 131, 0.4);
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
  transition: all 0.3s ease;
}

.token-chip:hover {
  transform: translateY(-2px);
  border-color: #42b883;
  background: rgba(0, 0, 0, 0.8);
}

.token-chip .label {
  font-size: 0.7rem;   
  color: #888;
  letter-spacing: 1px; 
}

.token-chip .value {
  color: #42b883;
  font-weight: 800;
  font-size: 1.1rem;
  text-shadow: 0 0 10px rgba(66, 184, 131, 0.3);
}


.topup-trigger {
 
  background: #42b883;
  border: none;
  color: white;
  width: 28px;   
  height: 28px;
  border-radius: 50%;
  cursor: pointer;
  

  display: flex;
  align-items: center;      
  justify-content: center;   
  

  font-size: 18px;         
  font-weight: bold;
  line-height: 1;           
  

  margin-left: 10px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 0 10px rgba(66, 184, 131, 0.3);
}

.topup-trigger:hover {
  transform: scale(1.1) rotate(90deg); 
}


.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.8);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}


.topup-modal {
  background: #1a1a1a;
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 40px;
  border-radius: 30px;
  width: 90%;
  max-width: 800px;
  text-align: center;
}

.modal-title { color: #42b883; margin-bottom: 10px; font-size: 2rem; }
.modal-subtitle { color: #888; margin-bottom: 40px; }


.package-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  margin-bottom: 30px;
}

.package-card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 30px 20px;
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.package-card:hover {
  background: rgba(66, 184, 131, 0.1);
  border-color: #42b883;
  transform: translateY(-10px);
}

.pkg-label { font-size: 0.8rem; color: #888; text-transform: uppercase; margin-bottom: 15px; }
.pkg-tokens { font-size: 2.5rem; color: white; font-weight: bold; margin-bottom: 10px; }
.pkg-tokens small { font-size: 1rem; color: #666; margin-left: 5px; }
.pkg-price { font-size: 1.5rem; color: gold; font-weight: bold; margin-bottom: 20px; }

.buy-btn {
  background: transparent;
  border: 1px solid #42b883;
  color: #42b883;
  padding: 8px 25px;
  border-radius: 50px;
  cursor: pointer;
  transition: 0.3s;
}
.package-card:hover .buy-btn { background: #42b883; color: white; }

.close-modal-btn { background: none; border: none; color: #555; cursor: pointer; text-decoration: underline; }
.delete-btn {
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  opacity: 0.4;
  transition: 0.3s;
}
.delete-btn:hover { opacity: 1; transform: scale(1.1); }

.project-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  padding: 2rem;
  text-align: left;
  cursor: pointer;
  transition: 0.3s;
}
.project-card:hover { transform: translateY(-5px); border-color: #646cff; background: rgba(100, 108, 255, 0.05); }

.cool-button {
  background: linear-gradient(45deg, #646cff, #747bff);
  border: none;
  padding: 0.8rem 2rem;
  border-radius: 10px;
  color: white;
  font-weight: bold;
  cursor: pointer;
}
.cool-button:disabled { opacity: 0.5; cursor: not-allowed; }

.back-btn { background: transparent; border: 1px solid #444; color: #888; padding: 0.5rem 1.5rem; border-radius: 8px; cursor: pointer; }
.back-btn:hover { color: white; border-color: white; }

.glow { position: absolute; width: 500px; height: 500px; background: radial-gradient(circle, rgba(66, 184, 131, 0.15) 0%, transparent 70%); z-index: -1; }



.fade-slide-enter-active, .fade-slide-leave-active { transition: all 0.5s ease; }
.fade-slide-enter-from { opacity: 0; transform: translateX(30px); }
.fade-slide-leave-to { opacity: 0; transform: translateX(-30px); }
</style>