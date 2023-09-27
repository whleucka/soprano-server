<section id='module-form'>
    <form method="POST" action="<?= $action ?>">
        <table id="form-table">
            <tbody>
            <?php if (!is_null($this->dataset)): ?>
                <?php foreach ($this->dataset as $column => $value):

                    if (in_array($column, $this->form_exclude)) {
                        continue;
                    }
                    $title = $this->form_columns[$column];
                    ?>
                    <tr>
                        <td class="text-right truncate form-label">
                            <label><?= $title ?></label>
                        </td>
                        <td><?= $this->control($column, $value) ?></td>
                    </tr>
                <?php
                endforeach; ?>
            <?php endif; ?>
            <tr>
                <td></td>
                <td>
                    <div class="pt-10">
                    <?php if ($this->dataset): ?>
                        <button class="md" title="Save" name="submit_type" value="save" type="submit"><?= !is_null(
                            $id
                        )
                            ? "Save"
                            : "Create" ?></button>
                        <?php if (!is_null($id)): ?>
                            <button class="md" title="Apply" name="submit_type" value="apply" type="submit">Apply</button>
                        <?php endif; ?>
                    <?php endif; ?>
                        <a href="<?= $cancel ?>" title="Cancel">
                            <button type="button" class="md">Cancel</button>
                        </a>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </form>
</section>
