<?php
/**
 * @var string $title
 */
/**
 * @var bool $datatables
 */
/**
 * @var bool $fontAwesome
 */
/**
 * @var string $logoHref
 */
?><!doctype html>
<html lang="<?php echo $_SESSION['locale']?>">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
	<title><?php echo $this->e($title)?> - <?php echo __('Entra in WEEE Open')?></title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
	<link rel="stylesheet" href="weee.css">
	<?php if (isset($datatables)) : ?>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.24.2/dist/bootstrap-table.min.css">
	<?php endif; ?>
	<?php if (isset($fontAwesome)) : ?>
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
	<?php endif; ?>
</head>
<body>
<?php echo $this->fetch('navbar', ['logoHref' => $logoHref ?? '/'])?>
<div class="container">
	<?php echo $this->section('content')?>
</div>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
		integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
		crossorigin="anonymous"></script>
<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>-->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
		integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
		crossorigin="anonymous"></script>
</body>
<?php if (isset($datatables)) : ?>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.24.2/dist/bootstrap-table.min.js"></script>
	<script>
		let $table = $('#candidates');
		$table.bootstrapTable({
			classes: 'table',
		});
	</script>
<?php endif; ?>
<script>
	$(document).ready(function() {
		$('.autoresize').each(function() {
			function autoresize() {
				$(this).height(0);
				$(this).height(this.scrollHeight);
			}
			$(this).on('input', autoresize.bind(this));
			autoresize.call(this);
		});
	});
</script>
</html>
