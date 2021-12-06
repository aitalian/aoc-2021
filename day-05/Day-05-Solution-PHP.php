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
 *  $ cat example-input.txt | php -f Day-05-Solution-PHP.php
 *
 *  # Read from default `input.txt`
 *  $ php -f Day-05-Solution-PHP.php
 *
 *  # Run tests on example data
 *  $ php -f Day-05-Solution-PHP.php test
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

class Coordinates
{
    private $gridCounts = array();

    public function addPoint($x1, $y1, $x2, $y2)
    {
        // Draw the full line by determine the whole range
        $fillX   = range($x1, $x2);
        $fillY   = range($y1, $y2);
        $fillMax = max(count($fillX), count($fillY));

        if (count($fillX) < $fillMax) {
            $fillX = array_fill(0, $fillMax, $fillX[0]);
        }

        if (count($fillY) < $fillMax) {
            $fillY = array_fill(0, $fillMax, $fillY[0]);
        }

        for ($n = 0; $n < $fillMax; $n++) {
            $c = "${fillX[$n]},${fillY[$n]}";

            if (array_key_exists($c, $this->gridCounts)) {
                $this->gridCounts[$c] += 1;
            } else {
                $this->gridCounts[$c] = 1;
            }
        }
    }

    public function getGridCounts()
    {
        return $this->gridCounts;
    }

    public function countOverlap()
    {
        return count(array_filter($this->getGridCounts(), function ($p) {
            return $p > 1;
        }));
    }
}

$p1CountCoordinates = new Coordinates;
$p2CountCoordinates = new Coordinates;

$lines = array(); // contains each set of coordinates (x1,y1 -> x2,y2) as k/v pairs

// Parse each line
foreach ($input as $row) {
    if (preg_match("/^(\d+),(\d+) -> (\d+),(\d+)$/", $row, $matches) !== false) {
        array_shift($matches);  // remove the first key
        list($x1, $y1, $x2, $y2) = $matches;
        $lines[] = array(
            'x1' => intval($x1),
            'y1' => intval($y1),
            'x2' => intval($x2),
            'y2' => intval($y2)
        );
    }
}

foreach ($lines as $line) {
    // Horizontal/Vertical
    if (($line['x1'] === $line['x2']) || ($line['y1'] === $line['y2'])) {
        $p1CountCoordinates->addPoint($line['x1'], $line['y1'], $line['x2'], $line['y2']);
    }

    // Diagonal/ALL
    $p2CountCoordinates->addPoint($line['x1'], $line['y1'], $line['x2'], $line['y2']);
}

$p1NumLinesOverlap = strval($p1CountCoordinates->countOverlap());
$p2NumLinesOverlap = strval($p2CountCoordinates->countOverlap());

// Declare our answers
$answers = array(
    $p1NumLinesOverlap,
    $p2NumLinesOverlap
);

print "Part One: Number of horizontal/vertical lines that overlap = ${answers[0]}\n";
print "Part Two: Number of lines including diagonally that overlap = ${answers[1]}\n";

if ($testMode) {
    $testAnswers = array_map('strval', (new ReadInputFile)->FromFile('example-input-answers.txt')->getInputArray());

    print "\n\nTESTS\n=====\n";

    for ($i = 0; $i < 2; $i++) {
        print "\tAnswer " . ($i + 1) . ": " . ($answers[$i] === $testAnswers[$i] ? "PASSED" : "failed! Expected Value: ${testAnswers[$i]}") . "\n";
    }
}
