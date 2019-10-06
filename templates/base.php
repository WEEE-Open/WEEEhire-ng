<?php
/** @var string $title */
?><!doctype html>
<html lang="<?= $_SESSION['locale'] ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $this->e($title) ?> - Join WEEE Open</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<link rel="stylesheet" href="weee.css">
	<?php if(isset($datatables)): ?>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.15.4/bootstrap-table.min.css" integrity="sha256-Bfo5E75379SXUZYuhGkuEc1K8EjSSpR/VF/axOVB8nw=" crossorigin="anonymous" />
	<?php endif; ?>
</head>
<body>
<?= $this->fetch('navbar') ?>
<div class="container">
<?= $this->section('content') ?>
</div>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>-->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script></body>
<?php if(isset($datatables)): ?>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.15.4/bootstrap-table.min.js" integrity="sha256-zuYwDcub7myT0FRW3/WZI7JefCjyTmBJIoCS7Rb9xQc=" crossorigin="anonymous"></script>
	<script>
		let $table = $('#candidates');
		$table.bootstrapTable({
			classes: 'table',
		});
	</script>
<?php endif; ?>
</html>
