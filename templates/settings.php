<?php
/** @var $users array */
/** @var $myname string */
/** @var $myuser string */
/** @var $scadenzaOld DateTime */

$this->layout('base', ['title' => __('Opzioni WEEEHire'), 'datatables' => true]);
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
                <td class="align-middle"><?= __('Scadenza Candidature ') ?>
                    <b>(
                        <?php if ($scadenzaOld === null) echo 'Nessuna Scadenza';
                            else {
                                $scadenzaOld = new \DateTime("@$scadenzaOld");
                                echo $scadenzaOld->format('d-m-Y');
                            }?>
                        ) :</b></td>
                <td><input type='date' class="form-control" name="scadenzaDate" /></td>
                <td><button type="button" class="btn btn-outline-danger" onclick="location='settings.php?reset=1'">&#x274C;</button></td>
            </tr>
            </tbody>
        </table>
        <div class="row justify-content-center">
            <button type="submit" class="btn btn-primary">Conferma</button>
        </div>
    </form>
</div>