<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\Answer;

class QuizSeeder extends Seeder
{


    public function run()
    {
        $questions = [
            [
                'text' => 'What is the best PHP framework?',
                'answers' => [
                    ['text' => 'Yii', 'is_correct' => false],
                    ['text' => 'Laravel', 'is_correct' => true],
                    ['text' => 'Symfony', 'is_correct' => false],
                    ['text' => 'CodeIgniter', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Which database is most commonly used with Laravel?',
                'answers' => [
                    ['text' => 'MongoDB', 'is_correct' => false],
                    ['text' => 'MySQL', 'is_correct' => true],
                    ['text' => 'Oracle', 'is_correct' => false],
                    ['text' => 'SQL Server', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Which HTTP method is idempotent?',
                'answers' => [
                    ['text' => 'POST', 'is_correct' => false],
                    ['text' => 'GET', 'is_correct' => true],
                    ['text' => 'PATCH', 'is_correct' => false],
                    ['text' => 'DELETE', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Which design pattern is widely used in Laravel?',
                'answers' => [
                    ['text' => 'Singleton', 'is_correct' => false],
                    ['text' => 'Factory', 'is_correct' => false],
                    ['text' => 'MVC', 'is_correct' => true],
                    ['text' => 'Observer', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Which command creates a new Laravel project?',
                'answers' => [
                    ['text' => 'php artisan make:project', 'is_correct' => false],
                    ['text' => 'composer create-project laravel/laravel', 'is_correct' => true],
                    ['text' => 'php artisan new laravel', 'is_correct' => false],
                    ['text' => 'laravel init', 'is_correct' => false],
                ],
            ],
        ];

        foreach ($questions as $q) {
            $question = Question::create(['question_text' => $q['text']]);
            // dd($question);
            foreach ($q['answers'] as $a) {
                Answer::create([
                    'question_id' => $question->id,
                    'answer_text' => $a['text'],
                    'is_correct' => $a['is_correct'],
                ]);
            }
        }
    }
}

