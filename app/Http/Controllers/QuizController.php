<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Question;
use App\Models\Answer;
use App\Models\Result;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class QuizController extends Controller
{
    // Show start page where user enters name
    public function showStart()
    {
        return view('quiz.start');
    }

    // Store user and put only id and user name in session
    public function storeUser(Request $request)
    {
        $request->validate(['name' => 'required|string|max:150']);
        $user = User::create(['name' => $request->input('name')]);
        session([
            'quiz_user_id'   => $user->id,
            'quiz_user_name' => $user->name,
        ]);
        return redirect('/quiz');
    }

    // Show the page that loads the JS quiz app
    public function showQuiz()
    {
        // ensure user exists in session
        if (! session()->has('quiz_user_id')) {
            return redirect('/');
        }
        return view('quiz.app'); 
    }

    // get radom question with answer
    public function getQuestion($order)
    {
        $userId = session('quiz_user_id');

        // get only that question randomly which is not been answered by current user yet.
        $question = Question::with(['answers' => function($q) {
            $q->select('id', 'question_id', 'answer_text');
        }])
        ->whereNotIn('id', function($sub) use ($userId) {
            $sub->select('question_id')
                ->from('results')
                ->where('user_id', $userId);
        })
        ->inRandomOrder()  
        ->first();

        // Return JSON
        return response()->json([
            'id' => $question->id,
            'question_text' => $question->question_text,
            'answers' => $question->answers->map(function($a) {
                return ['id' => $a->id, 'answer_text' => $a->answer_text];
            })
        ]);
    }

    // Submit answer or skip (AJAX JSON). Stores result with correct/wrong/skipped.
    public function submitAnswer(Request $request)
    {
        $request->validate([
            'question_id' => 'required|integer|exists:questions,id',
            'action' => ['required', Rule::in(['answer','skip'])],
            'answer_id' => 'nullable|integer|exists:answers,id',
        ]);

        $userId = session('quiz_user_id');
        if (! $userId) {
            return response()->json(['error' => 'Session user missing'], 403);
        }

        $questionId = (int) $request->input('question_id');
        $action = $request->input('action');

        if ($action === 'skip') {
            $status = 'skipped';
            $answerId = null;
        } else {
            $answerId = (int) $request->input('answer_id');
            $answer = Answer::where('id', $answerId)->where('question_id', $questionId)->first();
            if (! $answer) {
                return response()->json(['error' => 'Invalid answer'], 400);
            }
            $status = $answer->is_correct ? 'correct' : 'wrong';
        }

        // Insert result
        Result::create([
            'user_id' => $userId,
            'question_id' => $questionId,
            'answer_id' => $answerId,
            'status' => $status,
        ]);

        return response()->json(['status' => 'ok']);
    }

    // Return summary aggregates using SQL aggregation (COUNT + GROUP BY)
    public function summary()
    {
        $userId = session('quiz_user_id');
        if (! $userId) {
            return response()->json(['error' => 'Session user missing'], 403);
        }

        // Use SQL aggregate functions for counts grouped by status
        $rows = DB::table('results')
            ->select('status', DB::raw('COUNT(*) as total'))
            ->where('user_id', $userId)
            ->groupBy('status')
            ->get()
            ->pluck('total','status');

        // Ensure keys exist
        $summary = [
            'correct' => (int) ($rows->get('correct') ?? 0),
            'wrong'   => (int) ($rows->get('wrong') ?? 0),
            'skipped' => (int) ($rows->get('skipped') ?? 0),
        ];

        $total = array_sum($summary);
        $summary['total'] = $total;
        $summary['percentage'] = $total ? round(($summary['correct'] / $total) * 100, 2) : 0;

        return response()->json($summary);
    }
}
