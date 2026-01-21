<?php $view = get_view(); ?>

<div class="field">
    <div class="two columns alpha">
        <label for="delete_before"><?php echo __('Delete before'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('The events database table may get very large over time. This may eventually have an impact on the performance of your website. Use this form to reduce the size of the table by deleting events before a certain date.'); ?></p>
        <?php echo $view->formText('delete_before', null, ['placeholder' => 'yyyy-mm-dd']); ?>
    </div>
</div>
