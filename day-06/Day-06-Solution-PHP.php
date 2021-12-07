<?php

/**
 * Input data should be provided in `input.txt`.
 * Example data rows for testing should be in `example-input.txt`.
 * Tests will look for matching answers in `example-input-answers.txt` (one per line).
 * Input data can also be provided from stdin.
 *
 * Example CLI ustimer:
 * ```sh
 *  # Read from STDIN
 *  $ cat example-input.txt | php -f Day-06-Solution-PHP.php
 *
 *  # Read from default `input.txt`
 *  $ php -f Day-06-Solution-PHP.php
 *
 *  # Run tests on example data
 *  $ php -f Day-06-Solution-PHP.php test
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

class Lanternfish
{
    private $fish = array();

    private const newFishTimerValue = 8;
    private const resetTimerValue = 6;

    public function __construct(array $initialFishTimers = array()) {
        $this->fish = array_replace(array_fill(0, 10, 0), array_count_values($initialFishTimers));

        return $this;
    }

    public function addDays($numDays) {
        if ($numDays > 0) {
            for ($day = 1; $day <= $numDays; $day++) {
                $numChildren = array_shift($this->fish);                // index 0 will become children
                $this->fish[count(array_keys($this->fish))] = 0;        // add the last index since everything shifted left
                $this->fish[self::newFishTimerValue] += $numChildren;   // account for the children
                $this->fish[self::resetTimerValue] += $numChildren;     // account for the parent reset
            }
        }

        return $this;
    }

    public function getFishTotal() {
        return array_sum(array_values($this->fish));
    }
}

$initialFish = array_map('intval', array_map('trim', explode(',', $input[0])));

// 80 days (part 1) - 256 days (part 2)
$p1_fish = (new Lanternfish($initialFish))->addDays(80)->getFishTotal();
$p2_fish = (new Lanternfish($initialFish))->addDays(256)->getFishTotal();

// Declare our answers
$answers = array(
    $p1_fish,
    $p2_fish
);

print "Part One: Number of lanternfish after 80 days = ${answers[0]}\n";
print "Part Two: Number of lanternfish after 256 days = ${answers[1]}\n";

if ($testMode) {
    $testAnswers = array_map('intval', (new ReadInputFile)->FromFile('example-input-answers.txt')->getInputArray());

    print "\n\nTESTS\n=====\n";

    for ($i = 0; $i < 2; $i++) {
        print "\tAnswer " . ($i + 1) . ": " . ($answers[$i] === $testAnswers[$i] ? "PASSED" : "failed! Expected Value: ${testAnswers[$i]}") . "\n";
    }
}
