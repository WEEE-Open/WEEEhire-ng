<?php
/**
 * @var $user WEEEOpen\WEEEhire\User 
 */
/**
 * @var $edit bool 
 */
/**
 * @var \Psr\Http\Message\UriInterface $globalRequestUri 
 */
if (isset($edit) && $edit) {
    $readonly = '';
} else {
    $readonly = 'readonly';
}
?>

<form method="post">
    <div class="form-group row">
        <label for="name" class="col-md-2 col-lg-1 col-form-label"><?php echo __('Nome')?></label>
        <div class="col-md-4 col-lg-5">
            <input <?php echo $readonly?> id="name" name="name" type="text" required="required" class="form-control"
                    value="<?php echo $this->e($user->name)?>">
        </div>
        <label for="surname" class="col-md-2 col-lg-1 col-form-label"><?php echo __('Cognome')?></label>
        <div class="col-md-4 col-lg-5">
            <input <?php echo $readonly?> id="surname" name="surname" type="text" required="required" class="form-control"
                    value="<?php echo $this->e($user->surname)?>">
        </div>
    </div>
    <div class="form-group row">
        <label for="degreecourse" class="col-md-2 col-lg-1 col-form-label"><?php echo __('Corso di laurea')?></label>
        <div class="col-md-7 col-lg-6">
            <input <?php echo $readonly?> type="text" id="degreecourse" name="degreecourse" required="required"
                    class="form-control" value="<?php echo $this->e($user->degreecourse)?>">
        </div>
        <label for="year" class="col-md-1 col-form-label"><?php echo __('Anno')?></label>
        <div class="col-md-2 col-lg-4">
            <input <?php echo $readonly?> type="text" id="year" name="year" required="required" class="form-control"
                    value="<?php echo $this->e($user->year)?>">
        </div>
    </div>
    <div class="form-group row">
        <label for="matricola" class="col-md-2 col-lg-1 col-form-label"><?php echo __('Matricola')?></label>
        <div class="col-md-3 col-lg-4">
            <input <?php echo $readonly?> id="matricola" name="matricola" type="text" required="required" class="form-control"
                    value="<?php echo $this->e($user->matricola)?>">
        </div>
        <label for="area" class="col-md-2 col-lg-1 col-form-label"><?php echo __('Interesse')?></label>
        <div class="col-md-5 col-lg-6">
            <input <?php echo $readonly?> type="text" name="area" id="area" required="required" class="form-control"
                    value="<?php echo $this->e($user->area)?>">
        </div>
    </div>
    <div class="form-group">
        <label for="letter"><?php echo __('Lettera motivazionale')?></label>
        <textarea <?php echo $readonly?> id="letter" name="letter" cols="40" rows="5" required="required"
                class="form-control"><?php echo $this->e($user->letter)?></textarea>
    </div>
    <?php if ($edit) : ?>
        <div class="form-group">
            <button type="submit" name="edit" value="true" class="btn btn-primary"><?php echo __('Aggiorna dati')?></button>
            <a class="btn btn-secondary"
                    href="<?php echo $this->e(
                        \WEEEOpen\WEEEHire\Utils::appendQueryParametersToRelativeUrl(
                            $globalRequestUri,
                            ['edit' => null]
                        )
                          )?>"><?php echo __('Annulla')?></a>
        </div>
    <?php endif ?>
</form>
