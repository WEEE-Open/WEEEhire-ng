<?php
/**
 * @var $user WEEEOpen\WEEEhire\User 
 */
/**
 * @var $interview WEEEOpen\WEEEhire\Interview 
 */
/**
 * @var $edit bool 
 */
/**
 * @var $recruiters string[][] 
 */
/**
 * @var \Psr\Http\Message\UriInterface $globalRequestUri 
 */
/**
 * @var array $notes 
 */

$titleShort = sprintf(__('%s %s (%s)'), $this->e($user->name), $this->e($user->surname), $this->e($user->matricola));
$title = sprintf(__('%s - Colloquio'), $titleShort);
$this->layout('base', ['title' => $title, 'logoHref' => 'candidates.php']);
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="interviews.php"><?php echo __('Colloqui')?></a></li>
        <li class="breadcrumb-item active" aria-current="page"><?php echo $titleShort?></li>
    </ol>
</nav>

<?php if ($interview->status === true) : ?>
    <div class="alert alert-success" role="alert">
    <?php echo sprintf(__('Colloquio superato secondo %s'), $interview->recruiter)?>
    </div>
<?php elseif ($interview->status === false) : ?>
    <div class="alert alert-danger" role="alert">
    <?php echo sprintf(__('Colloquio fallito secondo %s'), $interview->recruiter)?>
    </div>
<?php endif ?>

<?php if ($interview->when === null) : ?>
    <div class="alert alert-warning" role="alert">
    <?php echo __('Colloquio da fissare')?>
    </div>
<?php else : ?>
    <div class="alert alert-info" role="alert">
    <?php echo sprintf(
        __('Colloquio fissato per il %s alle %s con <a href="https://t.me/%s">%s</a>. <a href="%s">🗓 Aggiungi al calendario.</a>'),
        $interview->when->format('Y-m-d'),
        $interview->when->format('H:i'),
        $interview->recruitertg,
        $interview->recruiter,
        $this->e(\WEEEOpen\WEEEHire\Utils::appendQueryParametersToRelativeUrl($globalRequestUri, ['download' => 'true']))
    )?>
    </div>
<?php endif ?>

<?php if ($interview->status === null && !$edit) : ?>
    <form method="post">
        <div class="form-group row">
            <label for="recruiter" class="col-md-1"><?php echo __('Recruiter')?></label>
            <div class="col-md-5">
                <select id="recruiter" name="recruiter" required="required" class="form-control">
                    <?php
                    $hit = false;
                    $therecruiter = $interview->recruiter ?? $user->recruiter;
                    foreach ($recruiters as $recruiter) :
                        if ($therecruiter === $recruiter[0]) :
                            $hit = true;
                            ?>
                            <option value="<?php echo $this->e($recruiter[1]) . '|' . $this->e($recruiter[0])?>"
                                    selected><?php echo $this->e($recruiter[0])?> (@<?php echo $this->e($recruiter[1])?>)
                            </option>
               <?php else : ?>
                            <option value="<?php echo $this->e($recruiter[1]) . '|' . $this->e($recruiter[0])?>"><?php echo $this->e($recruiter[0])?> (@<?php echo $this->e($recruiter[1])?>)</option>
               <?php endif;
                    endforeach; ?>
                    <?php if (!$hit) : ?>
                        <option disabled hidden selected class="d-none"></option>
                    <?php endif ?>
                </select>
            </div>
            <label for="when1" class="col-md-1 col-form-label"><?php echo __('Data')?></label>
            <div class="col-md-2">
                <input type="date" id="when1" name="when1" required="required" class="form-control"
                        placeholder="YYYY-MM-DD"
                        value="<?php echo $interview->when === null ? '' : $interview->when->format('Y-m-d')?>">
            </div>
            <label for="when2" class="col-md-1 col-form-label"><?php echo __('Ora')?></label>
            <div class="col-md-2">
                <input type="time" id="when2" name="when2" required="required" class="form-control" placeholder="HH:MM"
                        value="<?php echo $interview->when === null ? '' : $interview->when->format('H:i')?>">
            </div>
        </div>
        <div class="form-group text-center">
            <button name="setinterview" value="true" type="submit"
                    class="btn btn-primary"><?php echo __('Fissa colloquio')?></button>
            <button name="unsetinterview" value="true" type="submit"
                    class="btn btn-outline-danger"><?php echo __('Annulla colloquio')?></button>
        </div>
    </form>
<?php endif ?>

<?php if ($interview->status === true && !$edit) : /* ?>
    <form method="post">
    <div class="form-group row">
    <label for="safetyTestDate1" class="col-md-1 col-form-label"><?=__('Data')?></label>
    <div class="col-md-2">
                <input type="date" id="safetyTestDate1" name="safetyTestDate1" required="required" class="form-control"
                        placeholder="YYYY-MM-DD"
                        value="<?=$interview->safetyTestDate === null ? '' : $interview->safetyTestDate->format('Y-m-d')?>">
    </div>
    <label for="safetyTestDate2" class="col-md-1 col-form-label"><?=__('Ora')?></label>
    <div class="col-md-2">
                <input type="time" id="safetyTestDate2" name="safetyTestDate2" required="required" class="form-control" placeholder="HH:MM"
                        value="<?=$interview->safetyTestDate === null ? '' : $interview->safetyTestDate->format('H:i')?>">
    </div>
    <div class="col-md-3">
    <button name="setsafetyTestDate" value="true" type="submit"
                    class="btn btn-primary"><?=__('Fissa esame della sicurezza')?></button>
    </div>
    <div class="col-md-3">
    <button name="unsetsafetyTestDate" value="true" type="submit"
                    class="btn btn-outline-danger"><?=__('Cancella esame della sicurezza')?></button>
    </div>
    </div>
    </form>
    <?php */
endif ?>

<?php echo $this->fetch('userinfo', ['user' => $user, 'edit' => $edit])?>

<?php if (!$edit) : ?>
    <div class="form-group">
        <a class="btn btn-outline-secondary"
                href="<?php echo $this->e(
                    \WEEEOpen\WEEEHire\Utils::appendQueryParametersToRelativeUrl(
                        $globalRequestUri,
                        ['edit' => 'true']
                    )
                      )?>"><?php echo __('Modifica dati')?></a>
    </div>

    <h4 class="mt-5"><?php echo __('Note') ?></h4>
    <?php echo $this->fetch('notes', ['notes' => $notes]); ?>
    <?php
    $userNoted = false;
    foreach ($notes as $note) {
        $userNoted = $_SESSION['uid'] === $note['uid'];
        if ($userNoted) {
            break;
        }
    }
    ?>
    <?php if (!$userNoted) : ?>
    <form method="post" class="mt-3">
        <div class="form-group">
            <label for="notes"><b><?php echo __('Aggiungi nota') ?></b></label>
            <textarea id="notes" name="note" cols="40" rows="3"
                    class="form-control"></textarea>
        </div>
        <div class="form-group text-center">
            <button name="saveNote" value="true" type="submit"
                    class="btn btn-outline-primary my-1 mx-1"><?php echo __('Aggiungi nota')?></button>
        </div>
    </form>
    <?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label for="answers"><?php echo __('Commenti vari post-colloquio')?></label>
            <textarea id="answers" name="answers" cols="40" rows="10"
                    class="form-control"><?php echo $this->e($interview->answers)?></textarea>
        </div>
        <div class="form-group text-center">
            <button name="saveInterviewComments" value="true" type="submit" class="btn btn-outline-primary"><?php echo __('Salva commenti')?></button>
        </div>
        <div class="form-group text-center">
    <?php if ($interview->status === null && $interview->recruiter !== null && $interview->when !== null) : ?>
        <?php if ($interview->hold) : ?>
                    <button name="popHold" value="true" type="submit"
                            class="btn btn-info"><?php echo __('Togli dalla lista d\'attesa')?></button>
                <?php else : ?>
                    <button name="pushHold" value="true" type="submit"
                            class="btn btn-info"><?php echo __('Metti in lista d\'attesa')?></button>
                <?php endif; ?>
                <button name="approve" value="true" type="submit"
                        class="btn btn-success"><?php echo __('Colloquio passato')?></button>
                <button name="reject" value="true" type="submit"
                        class="btn btn-danger"><?php echo __('Colloquio fallito')?></button>
            <?php elseif ($interview->recruiter !== null && $interview->when !== null) : ?>
                <button name="limbo" value="true" type="submit"
                        class="btn btn-warning"><?php echo __('Rimanda nel limbo')?></button>
            <?php endif ?>
        </div>
    </form>
    <form method="post">
    <?php if ($user->invitelink !== null) : ?>
            <div class="alert alert-info" role="alert">
        <?php echo sprintf(__('Link d\'invito: %s'), $user->invitelink);?>
            </div>
    <?php endif ?>
        <div class="form-group text-center">
            <button name="invite" value="true" type="submit"
                    class="btn btn-primary"><?php echo __('Genera link d\'invito')?></button>
        </div>
    </form>
<?php endif ?>


<script src="resize.js"></script>
