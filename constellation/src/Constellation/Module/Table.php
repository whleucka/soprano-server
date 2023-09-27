<?php
use Constellation\Routing\Router; ?>
<section id="module-content-table">
    <?php if (!empty($this->table_filters)): ?>
        <div id="filters">
            <?php if ($this->table_filters):
                $search_term = $_SESSION[$this->module]["search"] ?? ""; ?>
                <form method="GET">
                    <input id="search-input" placeholder="..." name="search" type="search" value="<?= $search_term ?>"><button id="search-button" type="submit">Search</button>
                    <?php if ($search_term): ?>
                        <button type="submit" name="clear_search" id="search-clear">Clear</button>
                    <?php endif; ?>
                </form>
            <?php
            endif; ?>
        </div>
    <?php endif; ?>
    <div id="actions" class="flex">
        <?php if ($this->hasInsertPermission()):
            $record_link = Router::buildRoute(
                "module.create",
                $this->module
            ); ?>
        <div id="create-record">
            <a title="Create" href="<?= $record_link ?>">
            <button class="md" type="button">Create</button>
            </a>
        </div>
        <?php
        endif; ?>
        <?php if ($this->show_export_csv && !empty($this->dataset)): ?>
        <div id="export-csv">
            <form method="GET">
                <button class="md" type="submit" name="a" value="export_csv" title="Export CSV">Export CSV</button>
            </form>
        </div>
        <?php if (!empty($this->table_actions)): ?>
            <?php foreach ($this->table_actions as $action): ?>
                <form method="<?= $action["method"] ?>" onSubmit="<?= $action[
    "onSubmit"
] ?>">
                <input type="hidden" name="a" value="<?= $action["a"] ?>">
                <button class="<?= $action["size"] ?> <?= $action[
     "class"
 ] ?>" type="submit" title="<?= $action["title"] ?>" onClick="<?= $action[
    "onClick"
] ?>"><?= $action["title"] ?></button>
                </form>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php if (!empty($this->filter_links)): ?>
        <div id="filter-links" class="pt-10 pb-10">
        <?php
        $keys = array_keys($this->filter_links);
        $keys = array_map(function ($title) {
            $title_encode = urlencode($title);
            $class =
                isset($_SESSION[$this->module]["filter_link"]) &&
                $_SESSION[$this->module]["filter_link"] == $title
                    ? "active"
                    : "";
            echo "<span class='filter-link'>
            <a title='Filter on {$title}' href='?filter_link={$title_encode}'><button class='{$class}'>{$title} (<span class='filter-link-count mono' data-title='{$title_encode}'>...</span>)</button></a>
                </span>";
        }, $keys);
        echo implode(" ", $keys);
        ?>
        </div>
    <?php endif; ?>
    <?php if (empty($this->table_columns) && empty($this->dataset)): ?>
        <?php
        /** If there is no table columns or dataset,
                then we don't render the table */
        ?>
    <?php
        // Skip table columns that are null, allow empty strings // Used for the titles index
        // Skip the datum that has null title from above

        else: ?>
        <div class="table-wrapper">
            <table class="module-table">
            <thead>
            <tr class="blue-gradient">
                <?php
                $titles = [];
                $skip = [];
                foreach ($this->table_columns as $column => $title):

                    if (is_null($title)) {
                        $skip[] = $column;
                        continue;
                    }
                    $titles[] = $title;
                    $sort_column = urlencode($column);
                    if ($this->order_by_clause === $column) {
                        $sort_dir = $this->sort_clause == "ASC" ? " ▲" : " ▼";
                    } else {
                        $sort_dir = "";
                    }
                    ?>
                    <th class="header">
                       <a title="Order by <?= $title ?>" href="?order=<?= $sort_column ?>"><?= $title .
    $sort_dir ?></a>
                    </th>
                <?php
                endforeach;
                ?>
                <?php if ($this->show_table_actions): ?>
                    <th class="header"></th>
                <?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php if (!is_null($this->dataset) && !empty($this->dataset)): ?>
                <?php foreach ($this->dataset as $key => $datum):

                    $i = 0;
                    $datum = $this->override("table", $datum);
                    ?>
                    <tr>
                        <?php foreach ($datum as $column => $data):

                            if (
                                in_array($column, $skip) ||
                                !isset($titles[$i])
                            ) {
                                continue;
                            }
                            $id = $datum[$this->key_col];
                            $edit_link = Router::buildRoute(
                                "module.edit",
                                $this->module,
                                $id
                            );
                            $delete_action = Router::buildRoute(
                                "module.destroy",
                                $this->module,
                                $id
                            );
                            $value = $this->format($column, $data);
                            ?>
                            <?php if (
                                $column == $this->name_col &&
                                $this->hasEditPermission($id)
                            ): ?>
                                <td><a class="name-link" href="<?= $edit_link ?>" title="Edit"><?= $value ?></a></td>
                            <?php else: ?>
                                <td title="<?= isset($titles[$i])
                                    ? $titles[$i]
                                    : "" ?>"><?= $value ?></td>
                            <?php endif; ?>
                        <?php $i++;
                        endforeach; ?>
                        <?php if ($this->show_table_actions): ?>
                        <td class="pr-10">
                            <div class="action-row">
                                <?php foreach (
                                    $this->table_actions
                                    as $action
                                ): ?>

                                <?php endforeach; ?>
                                <?php if ($this->hasEditPermission($id)): ?>
                                    <a href="<?= $edit_link ?>">
                                        <button class="sm" type="button" title="Edit">Edit</button>
                                    </a>
                                <?php endif; ?>
                                <?php if ($this->hasDeletePermission($id)): ?>
                                    <form method="POST" action="<?= $delete_action ?>" onSubmit="return confirm('Are you sure you want to delete?\nThis action cannot be undone.');">
                                        <button class="sm" type="submit" title="Delete">Delete</button>
                                    </form>
                                <?php endif; ?>
                                <?php if (!empty($this->table_row_actions)): ?>
                                    <?php foreach (
                                        $this->table_row_actions
                                        as $action
                                    ): ?>
                                        <?php if (
                                            $this->hasActionPermission($id)
                                        ): ?>
                                        <form method="<?= $action[
                                            "method"
                                        ] ?>" onSubmit="<?= $action[
    "onSubmit"
] ?>">
                                        <input type="hidden" name="a" value="<?= $action[
                                            "a"
                                        ] ?>">
                                        <input type="hidden" name="id" value="<?= $id ?>">
                                        <button class="sm <?= $action[
                                            "class"
                                        ] ?>" type="submit" title="<?= $action[
    "title"
] ?>" onClick="<?= $action["onClick"] ?>"><?= $action["title"] ?></button>
                                        </form>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                <?php
                endforeach; ?>
            <?php else:
                $col_span = count($this->table_columns);
                if ($this->show_table_actions) {
                    $col_span++;
                }
                ?>
                <tr>
                    <td class="text-center" colspan="<?= $col_span ?>"><i>No data</i></td>
                </tr>
            <?php endif; ?>
            </tbody>
            </table>
        </div>
    <?php endif; ?>
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
        // How many page links before and after current page selector
        $offset = 1;
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
        <div class="limit-select">
            <form method="GET">
                <div class="flex justify-content-center align-items-center">
                    <label>Per Page</label>
                    <select class="ml-10" onChange="this.form.submit()" name="limit">
                        <?php
                        $options = [5, 10, 25, 50, 100, 500];
                        if (!in_array($this->limit_clause, $options)) {
                            $options[] = $this->limit_clause;
                            sort($options);
                        }
                        foreach ($options as $val) {
                            $selected =
                                $val == $this->limit_clause ? " selected" : "";
                            echo "<option value='{$val}'{$selected}>{$val}</option>";
                        }
                        ?>
                    </select>
                </div>
            </form>
        </div>
    <?php
    endif; ?>
</section>
