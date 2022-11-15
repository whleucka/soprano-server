<section id="module-table">
    <h3><?= $this->title ?></h3>
    <div class="alerts">
    </div>
    <div id="filters">
    </div>
    <div id="actions">
    </div>
    <div id="links">
    </div>
    <div class="table-wrapper">
        <table class="module-table">
        <thead>
        <tr class="blue-gradient">
            <?php
            use Constellation\Routing\Router;
            foreach ($this->table_columns as $column => $title): ?>
                <th class="header">
                    <?= $title ?>
                </th>
            <?php endforeach;
            ?>
            <?php if ($this->show_table_actions): ?>
                <th class="header"></th>
            <?php endif; ?>
        </tr>
        </thead>
        <tbody>
        <?php if (!is_null($this->dataset)): ?>
            <?php foreach ($this->dataset as $key => $datum): ?>
                <tr>
                    <?php foreach ($datum as $column => $data):

                        $id = $datum[$this->key_col];
                        $edit_link = Router::buildRoute(
                            "module.edit",
                            $this->module,
                            $id
                        );
                        $delete_link = Router::buildRoute(
                            "module.destroy",
                            $this->module,
                            $id
                        );
                        $data = $this->override($data);
                        $value = $this->format($column, $data);
                        ?>
                        <?php if ($column == $this->name_col): ?>
                            <td><a class="name-link" href="<?= $edit_link ?>" title="Edit"><?= $value ?></a></td>
                        <?php else: ?>
                            <td><?= $value ?></td>
                        <?php endif; ?>
                    <?php
                    endforeach; ?>
                    <?php if ($this->show_table_actions): ?>
                    <td class="pr-10">
                        <div class="action-row">
                            <?php foreach ($this->table_actions as $action): ?>

                            <?php endforeach; ?>
                            <?php if ($this->table_edit): ?>
                                <a href="<?= $edit_link ?>">
                                    <button class="sm" type="button" title="Edit">Edit</button>
                                </a>
                            <?php endif; ?>
                            <?php if ($this->table_delete): ?>
                                <a href="<?= $delete_link ?>">
                                    <button class="sm" type="button" title="Delete">Delete</button>
                                </a>
                            <?php endif; ?>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
        </table>
    </div>
    <?php if ($this->total_results >= 1):

        $prev =
            $this->page > 1
                ? '<a href="?page=1" title="First page">&laquo;</a> <a href="?page=' .
                    ($this->page - 1) .
                    '" title="Previous page">&lsaquo;</a>'
                : '<span class="disabled">&laquo;</span> <span class="disabled">&lsaquo;</span>';
        $next =
            $this->page < $this->total_pages
                ? '<a href="?page=' .
                    ($this->page + 1) .
                    '" title="Next page">&rsaquo;</a> <a href="?page=' .
                    $this->total_pages .
                    '" title="Last page">&raquo;</a>'
                : '<span class="disabled">&rsaquo;</span> <span class="disabled">&raquo;</span>';
        $offset = 2;
        ?>
        <div class="pagination">
            <?= $prev ?>
            <?php for ($i = $this->page - $offset; $i < $this->page; $i++): ?>
                <?php if ($i > 0): ?>
                    <a href="?page=<?= $i ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <a href="?page=<?= $i ?>"><strong><?= $this->page ?></strong></a>
            <?php for (
                $i = $this->page + 1;
                $i < $this->page + $offset + 1;
                $i++
            ): ?>
                <?php if ($i < $this->total_pages + 1): ?>
                    <a href="?page=<?= $i ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?= $next ?>
        </div>
    <?php
    endif; ?>
</section>
