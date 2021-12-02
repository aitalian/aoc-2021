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

$final_part1 = $x * $p1_y;
$final_part2 = $x * $p2_y;

print "Values of final horizontal position multiplied by final depth:\n";
print "Part One = ${final_part1}\n";
print "Part Two = ${final_part2}\n";
