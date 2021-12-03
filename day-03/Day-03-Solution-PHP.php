<?php

/** Not my proudest work */

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

# NOTE: Treating ALL values as strings for simplicity since we're dealing with binary (and because I'm tired)

$bin                    = array();  // array holding binary value digits split into kv pairs
$vertical_binary        = array();  // Array for each digit/position as a new binary number constructed top-down (vertical)
$bitcount_by_position   = array();  // array holding bits arranged by position

function gamma($bitcount_by_positionArr) {
    // most common bit
    $b = '';

    foreach ($bitcount_by_positionArr as $k => $v) {
        if ($v[0] == $v[1]) {
            $b .= 1;
        } else {
            $b .= ($v[0] > $v[1]) ? 0 : 1;
        }
    }

    return strval($b);
}

function epsilon($bitcount_by_positionArr) {
    // least common bit
    // note: epsilon could also be a bitflip of gamma
    $b = '';

    foreach ($bitcount_by_positionArr as $k => $v) {
        if ($v[0] == $v[1]) {
            $b .= 0;
        } else {
            $b .= ($v[0] < $v[1]) ? 0 : 1;
        }
    }

    return strval($b);
}

function input_to_bin_kv($input) {
    $bin = array();

    // Take each line value, split each digit and add each digit as it's own key/value pair based on it's position
    foreach ($input as $k => $v) {
        $bin[$k] = array_map('strval', str_split($v));
    }

    return $bin;
}

// Create a new array for each digit containing all values for that digit's position
function verticalBin($binArr) {
    $vertical_binary = array();

    foreach ($binArr as $k => $v) {
        foreach ($v as $nk => $nv) {
            $vertical_binary[$nk] .= $nv;
        }
    }

    return $vertical_binary;
}

function bitcountByPosition($verticalBinArr) {
    $bitcount_by_position = array();

    // Create an array that contains a count of each 1 or 0 for each digit's position
    foreach ($verticalBinArr as $k => $v) {
        $bitcount_by_position[$k] = array_count_values(array_map('intval', str_split((string) $v)));
    }

    return $bitcount_by_position;
}

function array_map_intval_str_split($str) {
    return array_map('intval', str_split((string) $str));
}

function getRating($inputList, $numPositions, $func = 'gamma') {
    for ($position = 0; $position < $numPositions; $position++) {
        if (!function_exists($func)) {
            throw new Exception("Function ${func} does not exist.");
        }

        $new_list = array();

        foreach ($inputList as $r => $b) {
            $pop_by_pos = array_map_intval_str_split($func(bitcountByPosition(verticalBin(input_to_bin_kv($inputList)))));

            if ($inputList[$r][$position] == $pop_by_pos[$position]) {
                $new_list[] = $b;
            }
        }

        $inputList = $new_list;

        if (count($new_list) == 1) {
            break;
        }
    }

    return $inputList[0];
}

$bin                  = input_to_bin_kv($input);
$vertical_binary      = verticalBin($bin);
$bitcount_by_position = bitcountByPosition($vertical_binary);

$gamma_binary      = gamma($bitcount_by_position);
$gamma_decimal     = strval(bindec($gamma_binary));
$epsilon_binary    = epsilon($bitcount_by_position);
$epsilon_decimal   = strval(bindec($epsilon_binary));
$power_consumption = strval($gamma_decimal * $epsilon_decimal);

$positions = count($vertical_binary);
$rows      = count($bin);

$most_popular_bit_by_position  = array_map_intval_str_split($gamma_binary);
$least_popular_bit_by_position = array_map_intval_str_split($epsilon_binary);

$oxygen_generator_rating_binary  = getRating($input, $positions, 'gamma');
$oxygen_generator_rating_decimal = strval(bindec($oxygen_generator_rating_binary));

$co2_scrubber_rating_binary      = getRating($input, $positions, 'epsilon');
$co2_scrubber_rating_decimal     = strval(bindec($co2_scrubber_rating_binary));

$life_support_rating = strval($oxygen_generator_rating_decimal * $co2_scrubber_rating_decimal);

// Declare our answers
$answers = array(
    $gamma_binary,
    $gamma_decimal,
    $epsilon_binary,
    $epsilon_decimal,
    $power_consumption,
    $oxygen_generator_rating_binary,
    $oxygen_generator_rating_decimal,
    $co2_scrubber_rating_binary,
    $co2_scrubber_rating_decimal,
    $life_support_rating
);

print "Raw values (not required for solution):";
print "\tGamma Rate = ${answers[0]} (binary) ${answers[1]} (decimal)\n";
print "\tEpsilon Rate = ${answers[2]} (binary) ${answers[3]} (decimal)\n";
print "\tOxygen Generator Rating = ${answers[5]} (binary) ${answers[6]} (decimal)\n";
print "\tCO2 Scrubber Rating = ${answers[7]} (binary) ${answers[8]} (decimal)\n";
print "\n\n";
print "Part One: Power consumption of the submarine = ${answers[4]}\n";
print "Part Two: Life support rating = ${answers[9]}\n";

if ($testMode) {
    $testAnswers = array_map('strval', (new ReadInputFile)->FromFile('example-input-answers.txt')->getInputArray());

    print "\n\nTESTS\n=====\n";

    for ($i = 0; $i < 10; $i++) {
        print "\tAnswer " . ($i + 1) . ": " . ($answers[$i] === $testAnswers[$i] ? "PASSED" : "failed! Expected Value: ${testAnswers[$i]}") . "\n";
    }
}
