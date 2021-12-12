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
 *  $ cat example-input.txt | php -f Day-07-Solution-PHP.php
 *
 *  # Read from default `input.txt`
 *  $ php -f Day-07-Solution-PHP.php
 *
 *  # Run tests on example data
 *  $ php -f Day-07-Solution-PHP.php test
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
            $this->input = explode(",", file_get_contents($filename));
        }

        return $this;
    }

    public function getInputArray()
    {
        return array_map('intval', $this->input);
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

// If we think about lining up the crabs with the least cost from left-right,
// we should be looking at the median position in the list.

/**
 * Find the median in the list.
 *
 * The median is the central number of a data set.
 * Arrange data points from smallest to largest and locate the central number.
 * This is the median.
 * If there are 2 numbers in the middle, the median is the average of those 2 numbers.
 */
function getMedian($input) {
    sort($input);

    $num_inputs = count($input);
    $middle     = floor(($num_inputs - 1) / 2);

    return ($num_inputs % 2) ? $input[$middle] : (
        // calculate average of 2 middle numbers
        ($input[$middle] + ($input[$middle + 1])) / 2
    );
}

/**
 * Find the average in the list;
 */
function getAverage($input) {
    return (array_sum($input) / count($input));
}

/**
 * Calculate fuel consumption cost based on an input array of positions,
 * and a given target position.
 * If $steppedCost = true, the cost increases +1 for each step;
 * otherwise the default cost is a constant 1/step taken.
 */
function calculateFuelCost($input, $targetPosition, $steppedCost = false) {
    $fuel_cost = 0;

    foreach ($input as $p) {
        $num_steps = abs($p - $targetPosition);

        if ($steppedCost) {
            // use sum of integers formula to increase cost +1 for each step
            // n(n+1)/2
            $fuel_cost += (($num_steps * ($num_steps + 1)) / 2);
        } else {
            $fuel_cost += $num_steps;
        }
    }

    return $fuel_cost;
}

// Determine total fuel cost to get to the median
$p1_median    = getMedian($input);
$p1_fuel_cost = calculateFuelCost($input, $p1_median);

// Part 2 looks at the average as the target, and the fuel cost is stepped by +1 for each move
/**
 * NOTE: Could not use `floor` or a single `round` function to wrap the average value.
 * It would work in one part, but not the other.
 * This solution seems to work best.
 * Round down to the nearest .00 - then round down again to make the number whole.
 * This way 4.9 => 5, 473.505 => 473, and we end up with the right answer in both cases.
 */
$p2_average_raw = getAverage($input);
$p2_average     = intval(round(round($p2_average_raw, 2, PHP_ROUND_HALF_DOWN), 0, PHP_ROUND_HALF_DOWN));
$p2_fuel_cost   = calculateFuelCost($input, $p2_average, $steppedCost = true);

// Declare our answers
// always use strings for answers so we don't deal with type issues on our tests
$answers = array_map('strval', array(
    $p1_median,
    $p1_fuel_cost,
    $p2_average,
    $p2_fuel_cost
));

print "Raw values (not required for solution):\n";
print "\tMedian position value = ${answers[0]}\n";
print "\tAverage position value = ${p2_average_raw} (raw) ${answers[2]} (rounded)\n";
print "\n\n";
print "Part One: Total fuel costs to align at median (least fuel used) = ${answers[1]}\n";
print "Part Two: Total fuel costs to align at average (lowest fuel cost) = ${answers[3]}\n";

if ($testMode) {
    $testAnswers = array_map('strval', (new ReadInputFile)->FromFile('example-input-answers.txt')->getInputArray());

    print "\n\nTESTS\n=====\n";

    for ($i = 0; $i < 4; $i++) {
        print "\tAnswer " . ($i + 1) . ": " . ($answers[$i] === $testAnswers[$i] ? "PASSED" : "failed! Expected Value: ${testAnswers[$i]} - Calculated: ${answers[$i]}") . "\n";
    }
}
