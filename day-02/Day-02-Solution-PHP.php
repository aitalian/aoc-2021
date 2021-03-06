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
 *  $ cat example-input.txt | php -f Day-02-Solution-PHP.php
 *
 *  # Read from default `input.txt`
 *  $ php -f Day-02-Solution-PHP.php
 *
 *  # Run tests on example data
 *  $ php -f Day-02-Solution-PHP.php test
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

$x    = 0;  // horizontal
$p1_y = 0;  // depth (part 1)
$p2_y = 0;  // depth (part 2)
$aim  = 0;

foreach ($input as $line) {
    $line = array_map('trim', explode(' ', $line));

    switch ($line[0]) {
        case 'forward':
            $x    += (int) $line[1];
            $p2_y += $aim * (int) $line[1];
            break;

        case 'down':
            $p1_y += (int) $line[1];
            $aim  += (int) $line[1];
            break;

        case 'up':
            $p1_y -= (int) $line[1];
            $aim  -= (int) $line[1];
            break;
    }
}

// Declare our answers
$answers = array(
    ($x * $p1_y),
    ($x * $p2_y)
);

print "Raw values (not required for solution):";
print "\tHorizontal = ${x}\tDepth1 = ${p1_y}\tDepth2 = ${p2_y}\tAim = ${aim}\n";
print "\n\n";
print "Values of final horizontal position multiplied by final depth:\n";
print "Part One = ${answers[0]}\n";
print "Part Two = ${answers[1]}\n";


if ($testMode) {
    $testAnswers = array_map('intval', (new ReadInputFile)->FromFile('example-input-answers.txt')->getInputArray());

    print "\n\nTESTS\n=====\n";

    for ($i = 0; $i < 2; $i++) {
        print "\tPart " . ($i + 1) . ": " . ($answers[$i] === $testAnswers[$i] ? "PASSED" : "failed! Expected Value: ${testAnswers[$i]}") . "\n";
    }
}
