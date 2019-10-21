<?php
/** @var $users array */
/** @var $myname string */
/** @var $myuser string */
/** @var $scadenzaOld DateTime */

$this->layout('base', ['title' => __('Opzioni WEEEHire'), 'datatables' => true]); //TODO: Controllare user -- VEDI TODO IN TEMPLATES
?>

<?= $this->fetch('adminnavbar', ['name' => $myname, 'user' => $myuser]) ?>

<div class="container-fluid">
    <h1><?= __('Opzioni WEEEHire') ?></h1>
    <p><i><?= __('Modifica scadenza candidature e altri parametri di configurazione WEEEHire') ?></i></p>
    <br>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <table class="table table-striped">
            <tbody>
            <tr>
                <td class="align-middle"><?= __('Scadenza Candidature ') ?><b>(<?php echo $scadenzaOld->format('d-m-Y') ?>) :</b></td>
                <td><input type='date' class="form-control" name="scadenzaDate" /></td>
            </tr>
            </tbody>
        </table>
        <div class="row justify-content-center">
            <button type="submit" class="btn btn-primary">Conferma</button>
        </div>
    </form>
</div>