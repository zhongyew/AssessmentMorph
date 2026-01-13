<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\History; // 引入刚才创建的模型
use Illuminate\Support\Facades\Http;

class GrammarController extends Controller
{
    // 1. 纯纠错接口：只负责返回结果，不存数据库
public function check(Request $request)
{
    $request->validate(['text' => 'required|string']);

    $response = Http::asForm()->post('https://api.languagetool.org/v2/check', [
        'text' => $request->text,
        'language' => 'en-US',
    ]);

    $data = $response->json();
    $matches = $data['matches'] ?? [];
    $correctedText = $request->text;
    
    // 这里的纠正逻辑保持不变，用于存储和显示最终结果
    foreach (array_reverse($matches) as $match) {
        if (!empty($match['replacements'])) {
            $replacement = $match['replacements'][0]['value'];
            $correctedText = substr_replace($correctedText, $replacement, $match['offset'], $match['length']);
        }
    }

    return response()->json([
        'success' => true,
        'corrected' => $correctedText,
        'matches' => $matches // 关键：把原始错误信息传给前端
    ]);
}

// 2. 新增收藏接口：只有前端点“收藏”时才调这个
public function store(Request $request)
{
    // 1. 确保这里接收了 tokens_used
    $request->validate([
        'input_text' => 'required|string',
        'output_result' => 'required|string',
        'tokens_used' => 'required|integer', // 确保接收整数
        'fee' => 'required|numeric'
    ]);

    // 2. 确保在 Create 的时候用了 $request 里的值
    $history = History::create([
        'input_text' => $request->input_text,
        'output_result' => $request->output_result,
        'tokens_used' => $request->tokens_used, // 这里千万不能写死成 1
        'fee' => $request->fee,
    ]);

    return response()->json(['success' => true]);
}

    // 获取所有历史记录接口
    public function getHistory() {
        return response()->json(History::orderBy('created_at', 'desc')->get());
    }

    // 删除历史记录接口
    public function deleteHistory($id) {
        History::destroy($id);
        return response()->json(['success' => true]);
    }
}