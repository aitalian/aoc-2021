<?php

/**
 * Input data should be provided in `input.txt`.
 * Example data rows for testing should be in `example-input.txt`.
 * Tests will look for matching answers in `example-input-answers.txt` (one per line).
 * Input data can also be provided from stdin.
 *
 * Example CLI usage:
 * ```sh
 *  # Read from STDIN
 *  $ cat example-input.txt | php -f Day-01-Solution-PHP.php
 *
 *  # Read from default `input.txt`
 *  $ php -f Day-01-Solution-PHP.php
 *
 *  # Run tests on example data
 *  $ php -f Day-01-Solution-PHP.php test
 * ```
 */

class ReadInputFile
{
    public $input = array();

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
            $this->input = array_filter(array_map('trim', explode("\n", file_get_contents($filename))));
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

$count_increases_part1 = 0;
$count_increases_part2 = 0;
$sum_previous_part2    = 0;

foreach ($input as $k => $v) {
    // Don't count the first item since there is no previous reading
    if ($k > 0) {
        if ($v > $input[$k - 1]) {
            $count_increases_part1++;
        }

        // Part 2 - three-measurement sliding window
        $a = (int) $v;
        $b = (array_key_exists(($k + 1), $input) ? (int) $input[$k + 1] : 0);
        $c = (array_key_exists(($k + 2), $input) ? (int) $input[$k + 2] : 0);

        $sum = $a + $b + $c;

        if ($sum > $sum_previous_part2) {
            $count_increases_part2++;
        }

        $sum_previous_part2 = $sum;
    }
}

// Declare our answers
$answers = array(
    $count_increases_part1,
    $count_increases_part2
);

print "Part One: Total measurements larger than the previous measurement = ${answers[0]}\n";
print "Part Two: Total sums larger than the previous sum = ${answers[1]}\n";

if ($testMode) {
    $testAnswers = array_map('intval', (new ReadInputFile)->FromFile('example-input-answers.txt')->getInputArray());

    print "\n\nTESTS\n=====\n";

    for ($i = 0; $i < 2; $i++) {
        print "\tPart " . ($i + 1) . ": " . ($answers[$i] === $testAnswers[$i] ? "PASSED" : "failed! Expected Value: ${testAnswers[$i]}") . "\n";
    }
}
