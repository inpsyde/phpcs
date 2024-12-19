<?php
// @phpcsSniff Syde.ControlStructures.AlternativeSyntax

const FLAGS = [
    'YES',
    'NO',
    'MAYBE',
    'WHATEVER',
];

$flag = FLAGS[rand(0, 3)];

if ($flag === 'MAYBE') {
    echo 'maybe';

    while ($flag !== 'YES') {
        $flag = 'YES';
    }
} elseif ($flag === 'NO') {
    echo 'no';
} else if ($flag === 'YES') {
    echo 'yes';
} else {
    echo 'non-empty value';
}

if ($flag === 'MAYBE') :
    echo 'maybe';

    while ($flag !== 'YES') {
        $flag = 'YES';
    }
elseif ($flag === 'NO') :
    echo 'no';
elseif ($flag === 'YES') :
    echo 'yes';
else :
    echo 'non-empty value';
endif;

$arrayOfFlags = [];
for ($i = 1; $i <= 10; $i++) {
    $arrayOfFlags[] = FLAGS[rand(0, 3)];
}

foreach ($arrayOfFlags as &$item) {
    $item = false;
}
unset($item);

switch ($flag) {
    case 'YES':
        echo 'It is true';
        break;

    case 'NO':
        echo 'It is false';
        break;
}

?>

<?php if ($flag === 'MAYBE') { // @phpcsWarningOnThisLine ?>
    <div>Maybe.</div>
    <?php while ($flag !== 'YES') {
        $flag = 'YES';
    }
} elseif ($flag === 'NO') { // @phpcsWarningOnThisLine
    while ($flag !== 'YES') { // @phpcsWarningOnThisLine
        $flag = 'YES';
        ?>
        <div>No. Yes.</div>
        <?php }
} else if ($flag === 'YES') { // @phpcsWarningOnThisLine
    echo 'yes';
} else { // @phpcsWarningOnThisLine
    echo 'non-empty value';
} ?>

<?php if ($flag === 'MAYBE') { // @phpcsWarningOnThisLine
    return;
} else if ($flag === 'NO') { // @phpcsWarningOnThisLine
    echo 'no';
} elseif ($flag === 'YES') { // @phpcsWarningOnThisLine ?>
    <div>Yes.</div>
<?php } else { // @phpcsWarningOnThisLine
    echo 'non-empty value';
} ?>

<?php
for ($i = 1; $i <= 10; $i++) { // @phpcsWarningOnThisLine ?>
    <div><?= $i ?></div>
<?php }

foreach ($arrayOfFlags as $item) { // @phpcsWarningOnThisLine ?>
    <div><?= $item ?></div>
<?php }

switch ($flag) { // @phpcsWarningOnThisLine
    case 'YES':
        ?>
        <div>Yes.</div>
        <?php
        break;

    case 'NO':
        echo 'It is false';
        break;
}
