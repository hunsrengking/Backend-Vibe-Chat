<?php

namespace App\Services;

use App\Models\BannedWord;
use Illuminate\Validation\ValidationException;

class SpamFilterService
{
    protected array $fallbackBannedWords = [
        'abuse', 'offensive', 'spam', 'scam', 'badword', 'hack', 'exploit', 'profanity'
    ];

    /**
     * Clean or check text for banned words/profanity.
     * Throws ValidationException if profanity is detected.
     */
    public function checkText(string $text, string $field = 'content'): void
    {
        $textLower = strtolower($text);

        // Fetch banned words from DB
        $dbBannedWords = BannedWord::pluck('word')->toArray();
        $bannedWords = array_unique(array_merge($this->fallbackBannedWords, $dbBannedWords));

        foreach ($bannedWords as $word) {
            $wordLower = strtolower($word);
            if (empty($wordLower)) {
                continue;
            }

            // Word boundary match or substring match
            if (str_contains($textLower, $wordLower)) {
                throw ValidationException::withMessages([
                    $field => ['The content contains inappropriate language or profanity.']
                ]);
            }
        }
    }

    /**
     * Censors banned words in a text (e.g., replaces with asterisks).
     */
    public function censorText(string $text): string
    {
        $dbBannedWords = BannedWord::pluck('word')->toArray();
        $bannedWords = array_unique(array_merge($this->fallbackBannedWords, $dbBannedWords));

        foreach ($bannedWords as $word) {
            if (empty($word)) {
                continue;
            }
            $replacement = str_repeat('*', strlen($word));
            $text = preg_replace('/\b' . preg_quote($word, '/') . '\b/i', $replacement, $text);
        }

        return $text;
    }
}
