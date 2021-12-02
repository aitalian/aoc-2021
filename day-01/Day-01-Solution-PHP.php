<?php

# ----- Handle reading stdin first, fallback to input.txt file if it exists

$input = array();

$stdin = fopen('php://stdin', 'r');

if (is_resource($stdin)) {
    stream_set_blocking($stdin, 0);

    while (($f = fgets($stdin)) !== false) {
        $input[] = trim($f);
    }

    fclose($stdin);
}

// Fallback to reading file
if (empty($input)) {
    if (file_exists('input.txt')) {
        $input = array_filter(array_map('trim', explode("\n", file_get_contents('input.txt'))));
    }
}

if (empty($input)) {
    die("No input was provided.\n");
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

print "Part One: Total measurements larger than the previous measurement: ${count_increases_part1}\n";
print "Part Two: Total sums larger than the previous sum: ${count_increases_part2}\n";
