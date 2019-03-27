<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;

class LangDetector extends Model {
    private $index = array();
    private $languages = array();
    public function addDocument($document, $language) {
        if(!isset($this->languages[$language])) {
            $this->languages[$language] = 0;
        }
        $words = $this->getWords($document);
        foreach($words as $match) {
            $trigrams = $this->getNgrams($match);
            foreach($trigrams as $trigram) {
                if(!isset($this->index[$trigram])) {
                    $this->index[$trigram] = array();
                }
                if(!isset($this->index[$trigram][$language])) {
                    $this->index[$trigram][$language] = 0;
                }
                $this->index[$trigram][$language]++;
            }
            $this->languages[$language] += count($trigrams);
        }
    }
    public function detect($document) {
        $words = $this->getWords($document);
        $trigrams = array();
        foreach($words as $word) {
            foreach($this->getNgrams($word) as $trigram) {
                if(!isset($trigrams[$trigram])) {
                    $trigrams[$trigram] = 0;
                }
                $trigrams[$trigram]++;
            }
        }
        $total = array_sum($trigrams);
        $scores = array();
        foreach($trigrams as $trigram => $count) {
            if(!isset($this->index[$trigram])) {
                continue;
            }
            foreach($this->index[$trigram] as $language => $lCount) {
                if(!isset($scores[$language])) {
                    $scores[$language] = 0;
                }
                $score = ($lCount / $this->languages[$language])
                    * ($count / $total);
                $scores[$language] += $score;
            }
        }
        arsort($scores);
        return key($scores);
    }
    public function getWords($document) {
        $document = strtolower($document);
        preg_match_all('/\w+/', $document, $matches);
        return $matches[0];
    }
    public function getNgrams($match, $n = 3) {
        $ngrams = array();
        $len = strlen($match);
        for($i = 0; $i < $len; $i++) {
            if($i > ($n - 2)) {
                $ng = '';
                for($j = $n-1; $j >= 0; $j--) {
                    $ng .= $match[$i-$j];
                }
                $ngrams[] = $ng;
            }
        }
        return $ngrams;
    }

}

?>
