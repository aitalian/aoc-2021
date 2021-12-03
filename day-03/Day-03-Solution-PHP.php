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
 *  $ cat example-input.txt | php -f Day-03-Solution-PHP.php
 *
 *  # Read from default `input.txt`
 *  $ php -f Day-03-Solution-PHP.php
 *
 *  # Run tests on example data
 *  $ php -f Day-03-Solution-PHP.php test
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

# NOTE: Treating ALL values as strings for simplicity (and because I'm tired)

$bin        = array();  // array holding binary value digits split into kv pairs
$newbin     = array();  // temp array to hold newly organized values for each digit's place
$bitbynum   = array();  // array holding bits arranged by number

function gamma($bitbynumArr) {
    // most common bit
    $b = '';

    foreach ($bitbynumArr as $k => $v) {
        $b .= ($v[0] >= $v[1]) ? 0 : 1;
    }

    return strval($b);
}

function epsilon($bitbynumArr) {
    // least common bit
    $b = '';

    foreach ($bitbynumArr as $k => $v) {
        $b .= ($v[0] < $v[1]) ? 0 : 1;
    }

    return strval($b);
}

foreach ($input as $k => $v) {
    $bin[$k] = array_map('strval', str_split($v));
}

foreach ($bin as $k => $v) {
    foreach ($v as $nk => $nv) {
        $newbin[$nk] .= $nv;
    }
}

foreach ($newbin as $k => $v) {
    $bitbynum[$k] = array_count_values(array_map('intval', str_split((string) $v)));
}

$gamma_binary      = gamma($bitbynum);
$gamma_decimal     = strval(bindec($gamma_binary));
$epsilon_binary    = epsilon($bitbynum);
$epsilon_decimal   = strval(bindec($epsilon_binary));
$power_consumption = strval($gamma_decimal * $epsilon_decimal);

// Declare our answers
$answers = array(
    $gamma_binary,
    $gamma_decimal,
    $epsilon_binary,
    $epsilon_decimal,
    $power_consumption
);

print "Raw values (not required for solution):";
print "\tGamma Rate = ${answers[0]} (binary) ${answers[1]} (decimal)\n";
print "\tEpsilon Rate = ${answers[2]} (binary) ${answers[3]} (decimal)\n";
print "\n\n";
print "Solution: Power consumption of the submarine = ${answers[4]}\n";

if ($testMode) {
    $testAnswers = array_map('strval', (new ReadInputFile)->FromFile('example-input-answers.txt')->getInputArray());

    print "\n\nTESTS\n=====\n";

    for ($i = 0; $i < 5; $i++) {
        print "\tAnswer " . ($i + 1) . ": " . ($answers[$i] === $testAnswers[$i] ? "PASSED" : "failed! Expected Value: ${testAnswers[$i]}") . "\n";
    }
}
