<?php

/**
 * NOTE: The tests for Part 2 appear to have incorrect values shown in the puzzle!
 * I was able to still compute the correct answer to submit for both parts,
 * so the example values given must be wrong.
 * 
 * This solution is also not optimized. I could/should have used array_reduce.
 * My code continued to grow in verbosity while troubleshooting why my tests were not passing.
 */

/**
 * Input data should be provided in `input.txt`.
 * Example data rows for testing should be in `example-input.txt`.
 * Tests will look for matching answers in `example-input-answers.txt` (one per line).
 * Input data can also be provided from stdin.
 *
 * Example CLI usage:
 * ```sh
 *  # Read from STDIN
 *  $ cat example-input.txt | php -f Day-04-Solution-PHP.php
 *
 *  # Read from default `input.txt`
 *  $ php -f Day-04-Solution-PHP.php
 *
 *  # Run tests on example data
 *  $ php -f Day-04-Solution-PHP.php test
 * ```
 */

class ReadInputFile
{
    private $input = array();

    public function FromSTDIN($fallbackFromFile = true)
    {
        $stdin = fopen('php://stdin', 'r');

        if (is_resource($stdin)) {
            stream_set_blocking($stdin, 0);

            while (($f = fgets($stdin)) !== false) {
                $this->input[] = trim($f);
            }

            fclose($stdin);
        }

        if (empty($this->input)) {
            if ($fallbackFromFile) {
                $this->FromFile('input.txt');
            }
        }

        return $this;
    }

    public function FromFile($filename = 'input.txt')
    {
        if (file_exists($filename)) {
            $this->input = explode("\n", file_get_contents($filename));
        }

        return $this;
    }

    public function getInputArray()
    {
        return $this->input;
    }
}

$testMode = false;

if (isset($argv) && !empty($argv[1])) {
    if ($argv[1] == "test") {
        $testMode = true;
    }
}

if ($testMode) {
    $input = (new ReadInputFile)->FromFile('example-input.txt')->getInputArray();
} else {
    $input = (new ReadInputFile)->FromSTDIN(true)->getInputArray();
}

# ----- BEGIN Puzzle

class BingoCard
{
    private const marker = 'X';

    protected $card;
    protected $originalCard;    // holds the original, unchanged values of the card
    protected $sumUnmarked;     // sum of the remaining unmarked numbers

    private function addRow($rowNum, $values)
    {
        $this->card[$rowNum]         = $values;
        $this->originalCard[$rowNum] = $values;
        $this->sumUnmarked();

        return $this;
    }

    public static function getBingoCardsFromInput($input)
    {
        $bingoCards = array();

        // filter removes empty rows, so we end up with row count divisible by 5
        $rawBingoCards = array_filter(array_slice($input, 2));
        $rowCount      = count($rawBingoCards);

        if ($rowCount % 5 !== 0) {
            throw new Exception("Incomplete card data");
        }

        // Counters
        $bingoCardNum = -1; // start at -1 since it will be incremented first
        $row          = 0;

        foreach ($rawBingoCards as $line) {
            if ($row == 0 || $row >= 5) {
                // start a new card
                $row = 0;
                $bingoCardNum++;
                $bingoCards[$bingoCardNum] = new BingoCard;
            }

            $bingoCards[$bingoCardNum]->addRow($row, array_values(array_filter(
                array_map('trim', explode(' ', $line)),
                function ($x) {
                    return ($x !== '');
                }
            )));

            $row++;
        }

        return $bingoCards;
    }

    public function markNumber($number)
    {
        foreach ($this->card as $key => $value) {
            $markPos = array_search($number, $value, true);

            if ($markPos !== false) {
                $this->card[$key][$markPos] = self::marker;
            }
        }

        $this->sumUnmarked();

        return $this;
    }

    // Unmark all numbers (restore original card)
    public function reset()
    {
        $this->card = $this->originalCard;
        $this->sumUnmarked();

        return $this;
    }

    public function getSumUnmarked()
    {
        return $this->sumUnmarked;
    }

    private function getUnmarkedNumbers()
    {
        $unmarkedNumbers = array();

        foreach ($this->card as $row) {
            foreach ($row as $k => $v) {
                if ($v !== self::marker) {
                    $unmarkedNumbers[] = $v;
                }
            }
        }

        return $unmarkedNumbers;
    }

    private function sumUnmarked()
    {
        $this->sumUnmarked = 0;

        foreach ($this->getUnmarkedNumbers() as $n) {
            $this->sumUnmarked += (int) $n;
        }
    }

    public function isWinner()
    {
        // Check each row
        foreach ($this->card as $row) {
            $rowCounts = array_count_values($row);

            if ($rowCounts[self::marker] === count($this->card)) {
                return true;
            }
        }

        // Check each column
        for ($c = 0; $c < count($this->card); $c++) {
            $colCounts = array_count_values(array_column($this->card, $c));

            if ($colCounts[self::marker] === count($this->card)) {
                return true;
            }
        }

        return false;
    }
}

$winningNumbers = array_filter(array_map('trim', explode(',', $input[0])));
$bingoCards     = BingoCard::getBingoCardsFromInput($input);

$p1winningSumOfUnmarkedNumbers = 0;
$p1winningNumberCalled         = 0;
$p1winningFinalScore           = 0;
$p1winningBoardNumber          = null;

$cardsRemaining = array_keys($bingoCards);

$p2winningSumOfUnmarkedNumbers = 0;
$p2winningNumberCalled         = 0;
$p2winningFinalScore           = 0;

foreach ($winningNumbers as $winningNumber) {
    // Part 1: Get the first winner
    foreach ($bingoCards as $cardNumber => $bingoCard) {
        $bingoCard->markNumber($winningNumber);

        if ($bingoCard->isWinner()) {
            // declare the winner and get the score

            if ($p1winningBoardNumber === null) {
                // First board to win
                $p1winningBoardNumber          = strval($cardNumber + 1);   // because arrays start at 0
                $p1winningNumberCalled         = strval($winningNumber);
                $p1winningSumOfUnmarkedNumbers = strval($bingoCard->getSumUnmarked());
                $p1winningFinalScore           = strval($p1winningSumOfUnmarkedNumbers * $p1winningNumberCalled);
            }

            if (count($cardsRemaining) === 1) {
                // Last board to win
                $p2winningBoardNumber          = strval($cardNumber + 1);   // because arrays start at 0
                $p2winningNumberCalled         = strval($winningNumber);
                $p2winningSumOfUnmarkedNumbers = strval($bingoCard->getSumUnmarked());
                $p2winningFinalScore           = strval($p2winningSumOfUnmarkedNumbers * $p2winningNumberCalled);
            }

            unset($cardsRemaining[$cardNumber]);
        }
    }
}

// Declare our answers
$answers = array(
    $p1winningBoardNumber,
    $p1winningSumOfUnmarkedNumbers,
    $p1winningNumberCalled,
    $p1winningFinalScore,
    $p2winningBoardNumber,
    $p2winningSumOfUnmarkedNumbers,
    $p2winningNumberCalled,
    $p2winningFinalScore
);

print "Raw values (not required for solution):\n";
print "\tFirst Winning Board Number = ${answers[0]}\n";
print "\tFirst Winning Sum of Unmarked Numbers = ${answers[1]}\n";
print "\tFirst Winning Number Called = ${answers[2]}\n";
print "\tLast Winning Board Number = ${answers[4]}\n";
print "\tLast Winning Sum of Unmarked Numbers = ${answers[5]}\n";
print "\tLast Winning Number Called = ${answers[6]}\n";
print "\n\n";
print "Part One: First Winning Final Score = ${answers[3]}\n";
print "Part Two: Last Winning Final Score = ${answers[7]}\n";

if ($testMode) {
    $testAnswers = array_map('strval', (new ReadInputFile)->FromFile('example-input-answers.txt')->getInputArray());

    print "\n\nTESTS\n=====\n";

    for ($i = 0; $i < 8; $i++) {
        print "\tAnswer " . ($i + 1) . ": " . ($answers[$i] === $testAnswers[$i] ? "PASSED" : "failed! Expected Value: ${testAnswers[$i]}") . "\n";
    }
}
