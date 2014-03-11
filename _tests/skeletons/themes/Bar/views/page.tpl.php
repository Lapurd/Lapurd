<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= $page_title ?></title>
    <meta charset="UTF-8">
    <?= $favicon ?>
    <?= $assets ?>
</head>
<body>
    <section><?= $regions['left'] ?></section>
    <main>
        <p>This is from theme 'Bar'.</p>
        <section><?= $regions['main'] ?></section>
        <?= $content ?>
    </main>
    <section><?= $regions['right'] ?></section>
</body>
</html>
