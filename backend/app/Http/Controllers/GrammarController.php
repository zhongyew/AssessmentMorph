<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\History; 
use Illuminate\Support\Facades\Http;

class GrammarController extends Controller
{
    // 1. Check API and response only
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
    
    
    foreach (array_reverse($matches) as $match) {
        if (!empty($match['replacements'])) {
            $replacement = $match['replacements'][0]['value'];
            $correctedText = substr_replace($correctedText, $replacement, $match['offset'], $match['length']);
        }
    }

    return response()->json([
        'success' => true,
        'corrected' => $correctedText,
        'matches' => $matches 
    ]);
}

// 2. Response when user click favourite button
public function store(Request $request)
{
    // 1. verify the token used
    $request->validate([
        'input_text' => 'required|string',
        'output_result' => 'required|string',
        'tokens_used' => 'required|integer'
        
    ]);

    // 2. Deduct the token before create
    $history = History::create([
        'input_text' => $request->input_text,
        'output_result' => $request->output_result,
        'tokens_used' => $request->tokens_used, // 这里千万不能写死成 1
        
    ]);

    return response()->json(['success' => true]);
}

    // Get history
    public function getHistory() {
        return response()->json(History::orderBy('created_at', 'desc')->get());
    }

    // Delete history
    public function deleteHistory($id) {
        History::destroy($id);
        return response()->json(['success' => true]);
    }
}