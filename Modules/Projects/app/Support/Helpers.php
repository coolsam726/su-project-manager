<?php

namespace Modules\Projects\Support;

class Helpers
{
    public function makeShortName(string $name): string
    {
        // Pick the first initials of the words in the title case of the name
        $words = $this->removeArticlesAndConjunctions($name);
        // Use the string helpers
        // ucwords then implode, then filter only upper case chars
        $single = str(ucwords($words))->replace(' ', '');
        return implode('',array_filter(str_split($single), function($char) {
            return ctype_upper($char);
        }));
    }

    public function removeArticlesAndConjunctions($sentence): string
    {
        // Define the list of words to remove
        $wordsToRemove = ['a', 'an', 'the', 'for', 'and', 'nor', 'but', 'or', 'yet', 'so', 'of', 'to', 'in', 'on', 'at', 'with', 'by', 'as', 'but', 'for', 'from', 'into', 'like', 'near', 'of', 'off', 'on', 'onto', 'out', 'over', 'past', 'to', 'upon', 'with'];

        // Split the sentence into words
        $words = explode(' ', strtolower($sentence));

        // Filter out the words to remove
        $filteredWords = array_filter($words, function($word) use ($wordsToRemove) {
            return !in_array($word, $wordsToRemove);
        });

        // Join the remaining words back into a sentence
        return implode(' ', $filteredWords);
    }
}