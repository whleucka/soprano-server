<?php

namespace Constellation\Module;

use Closure;
use Constellation\Alerts\Flash;
use Constellation\Authentication\Auth;
use Constellation\Controller\Controller;
use Constellation\Database\DB;
use Constellation\Http\Request;
use Constellation\Routing\Router;
use Constellation\Validation\Validate;
use Constellation\View\Control;
use Constellation\View\Format;
use Exception;
use PDO;
use PDOException;

class Module
{
    // The database wrapper
    protected DB $db;
    // Show table new button
    protected bool $allow_insert = true;
    // Show table edit button
    protected bool $allow_edit = true;
    // Show table delete button
    protected bool $allow_delete = true;
    // The dataset
    protected ?array $dataset = null;
    // The query (SQL)
    private string $query = "";
    // Primary key column
    protected string $key_col = "id";
    // Table edit link / breadcrumb name
    protected string $name_col = "";
    // Current page
    protected int $page = 1;
    // Total pages
    protected int $total_pages = 1;
    // Total results
    protected int $total_results = 0;
    // The request
    protected Request $request;
    // Module h3 title
    public ?string $title = null;
    // Module table (SQL)
    protected string $table = "";
    // Extra module tables
    protected array $extra_tables = [];
    // Where clause array
    private array $where = [];
    // Where clause params
    private array $params = [];
    // Where clause string (SQL)
    private ?string $where_clause = null;
    // Having clause array (SQL)
    protected ?string $having_clause = null;
    // Group by clause (SQL)
    protected ?string $group_by_clause = null;
    // Order by clause (SQL)
    protected ?string $order_by_clause = null;
    // Sort direction clause (SQL)
    protected string $sort_clause = "ASC";
    // Offset clause (SQL)
    protected ?int $offset_clause = null;
    // Limit clause (SQL)
    protected int $limit_clause = 25;
    // Table columns
    protected array $table_columns = [];
    // Show table actions cell
    protected bool $show_table_actions = true;
    // Show the export csv action
    protected bool $show_export_csv = true;
    // Table filters (search, date, etc)
    protected array $table_filters = [];
    // Table formatting
    protected array $table_format = [];
    // Table filter links
    protected array $filter_links = [];
    // Table actions (top, like Create, Export CSV)
    protected array $table_actions = [];
    // Table row actions (table row, like Edit, Delete)
    protected array $table_row_actions = [];
    // Form columns
    protected array $form_columns = [];
    // Form default values
    protected array $form_default = [];
    // Form control
    protected array $form_control = [];
    // Validation array
    protected array $validate = [];
    // Exclude columns from update/create
    // Part of the dataset, but not rendered in the form
    protected array $form_exclude = [];

    public function __construct(public ?string $module = null)
    {
        // Current user
        $this->user = Auth::user();
        // Get database instance
        $this->db = DB::getInstance();
        // Get request
        $this->request = Request::getInstance();
    }

    /**
     * Get the current url (full)
     */
    protected function getCurrentUrl()
    {
        return (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on"
            ? "https"
            : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }

    /**
     * Log user session
     */
    protected function logSession()
    {
        // Log session
        $this->db->query(
            "INSERT INTO sessions SET
                user_id = ?,
                ip = ?,
                url = ?,
                created_at = NOW()",
            $this->user->id,
            $_SERVER["REMOTE_ADDR"],
            $this->getCurrentUrl()
        );
    }

    /**
     * Process the table request
     */
    protected function processTableRequest()
    {
        $this->handleSettings();
        $this->handleFilters();
        $this->handleActions();
        $this->compileWhereClause();
    }

    /**
     * Process the form request
     */
    protected function processFormRequest($id)
    {
        $this->handleActions();
        if ($id) {
            $this->addWhereClause("{$this->key_col} = ?", [$id]);
        }
        $this->compileWhereClause();
    }

    /**
     * Refresh the table view
     */
    protected function refreshTable()
    {
        $table_link = Router::buildRoute("module.index", $this->module);
        $get_params = "{$table_link}?page={$this->page}";
        header("Location: {$get_params}");
        exit();
    }

    /**
     * Refresh the form view
     */
    protected function refreshForm($id = null)
    {
        if ($id) {
            $edit_link = Router::buildRoute("module.edit", $this->module, $id);
            header("Location: {$edit_link}");
            exit();
        } else {
            $create_link = Router::buildRoute("module.create", $this->module);
            header("Location: {$create_link}");
            exit();
        }
    }

    /**
     * Handle page number, sort, limit clause, etc
     */
    protected function handleSettings()
    {
        $this->setPage();
        $this->setOrderSort();
        $this->setLimit();
    }

    /**
     * Handle search, filter links, etc
     */
    protected function handleFilters()
    {
        $this->handleSearchFilter();
        $this->handleFilterLinks();
    }

    /**
     * Handle the table search filter
     */
    protected function handleSearchFilter()
    {
        $search = null;
        if (isset($this->request->data["clear_search"])) {
            $this->page = 1;
            unset($_SESSION[$this->module]["search"]);
            $this->refreshTable();
        } elseif (isset($this->request->data["search"])) {
            $this->page = 1;
            $search = trim($this->request->data["search"]);
            $_SESSION[$this->module]["search"] = $search;
        } elseif (isset($_SESSION[$this->module]["search"])) {
            $search = $_SESSION[$this->module]["search"];
        }
        if ($search) {
            $clause = [];
            foreach ($this->table_filters as $filter) {
                $clause[] = "{$filter} LIKE ?";
            }
            $where_clause = implode(" OR ", $clause);
            $params = array_fill(
                0,
                count($this->table_filters),
                "%" . $search . "%"
            );
            $this->addWhereClause($where_clause, $params);
        }
    }

    /**
     * Handle the active filter link
     */
    protected function handleFilterLinks()
    {
        $filter_link = null;
        if (isset($this->request->data["filter_link"])) {
            // There is a filter link request
            $filter_link = $this->request->data["filter_link"];
            // Set the session for persistence
            $_SESSION[$this->module]["filter_link"] = $filter_link;
        } elseif (isset($_SESSION[$this->module]["filter_link"])) {
            // The filter is available in the session
            $filter_link = $_SESSION[$this->module]["filter_link"];
        }
        if ($filter_link) {
            // We can apply the filter link from the request
            $this->addWhereClause($this->filter_links[$filter_link]);
        } else {
            // If the filter_links array is not empty, then set session
            // such that the filter button is active
            $key = array_key_first($this->filter_links);
            if (isset($this->filter_links[$key])) {
                $_SESSION[$this->module]["filter_link"] = $key;
                // Add the where clause for the filter link
                $this->addWhereClause($this->filter_links[$key]);
            }
        }
    }

    /**
     * Add a table action or a table row action to the view
     */
    protected function addTableAction(
        string $a,
        string $title,
        $row = true,
        string $method = "POST",
        string $onSubmit = "",
        string $onClick = "",
        string $confirmation = "",
        string $class = ""
    ) {
        if ($confirmation) {
            $onSubmit = "return confirm('" . $confirmation . "')";
        }

        $target = $row ? "table_row_actions" : "table_actions";
        $this->$target[] = [
            "a" => $a,
            "title" => $title,
            "method" => $method,
            "confirmation" => $confirmation,
            "onSubmit" => $onSubmit,
            "onClick" => $onClick,
            "class" => $class,
        ];
    }

    /**
     * Handle page actions
     */
    protected function handleActions()
    {
        if (isset($this->request->data["a"])) {
            $this->action($this->request->data["a"]);
        }
    }

    /**
     * Action switch
     */
    protected function action($action)
    {
        switch ($action) {
            case "export_csv":
                $this->compileWhereClause();
                $this->exportCsv();
                break;
            case "cancel":
                Flash::addFlash("info", "Action cancelled");
                $this->refreshTable();
                break;
            case "filter_count":
                $this->filterCount();
                break;
            case "sidebar":
                $this->toggleSidebar();
            default:
                foreach (
                    [$this->table_actions, $this->table_row_actions]
                    as $action_type
                ) {
                    $found = array_filter($action_type, function ($row) use (
                        $action
                    ) {
                        return $row["a"] === $action;
                    });
                    if ($found) {
                        $this->processTableAction($action);
                        return;
                    }
                }
                Flash::addFlash("error", "Unknown action");
                $this->refreshTable();
        }
    }

    /**
     * Process a table action
     * Only process actions via addTableAction
     */
    protected function processTableAction(string $action)
    {
    }

    /**
     * Export list view to CSV
     */
    protected function exportCsv()
    {
        header("Content-Type: text/csv");
        header('Content-Disposition: attachment; filename="csv_export.csv"');
        $fp = fopen("php://output", "wb");
        $csv_headers = $skip = [];
        foreach ($this->table_columns as $column => $title) {
            $column = $this->stripToAlias($column);
            if (is_null($title)) {
                $skip[] = $column;
                continue;
            }
            $csv_headers[] = $title;
        }
        fputcsv($fp, $csv_headers);
        $this->limit_clause = 10_000;
        $this->page = 1;
        while ($this->page <= $this->total_pages) {
            $this->getTableData();
            foreach ($this->dataset as $item) {
                $item = array_filter(
                    $item,
                    function ($k) use ($skip) {
                        return !in_array($k, $skip);
                    },
                    ARRAY_FILTER_USE_KEY
                );
                $row = $this->override("csv", $item);
                $values = array_values($row);
                fputcsv($fp, $values);
            }
            $this->page++;
        }
        fclose($fp);
        exit();
    }

    /**
     * Set the table page
     */
    protected function setPage()
    {
        if (isset($this->request->data["page"])) {
            $this->page = (int) $this->request->data["page"];
            $_SESSION[$this->module]["page"] = $this->page;
        } elseif (isset($_SESSION[$this->module]["page"])) {
            $this->page = $_SESSION[$this->module]["page"];
        }
    }

    /**
     * Check if table column exists
     */
    protected function tableColumnExists($column)
    {
        // We could use key_exists here, but there could be subqueries
        foreach ($this->table_columns as $table_column => $title) {
            // Doing this because the column could be aliased
            $table_column = $this->stripToAlias($table_column);
            if ($table_column == $column) {
                return true;
            }
        }
        return false;
    }

    /**
     * Set the table sort
     */
    protected function setOrderSort()
    {
        if (isset($this->request->data["order"])) {
            if ($this->tableColumnExists($this->request->data["order"])) {
                $this->order_by_clause = $this->request->data["order"];
                $_SESSION[$this->module]["order"] = $this->order_by_clause;
                if (isset($_SESSION[$this->module]["sort"])) {
                    if ($_SESSION[$this->module]["sort"] == "ASC") {
                        $this->sort_clause = "DESC";
                    } else {
                        $this->sort_clause = "ASC";
                    }
                }
                $_SESSION[$this->module]["sort"] = $this->sort_clause;
                // To prevent refreshing the page and changing the sort dir
                $this->refreshTable();
            }
        } elseif (isset($_SESSION[$this->module]["order"])) {
            $this->order_by_clause = $_SESSION[$this->module]["order"];
            if (isset($_SESSION[$this->module]["sort"])) {
                $this->sort_clause = $_SESSION[$this->module]["sort"];
            }
        }
    }

    /**
     * Set the limit (rows per page)
     */
    protected function setLimit()
    {
        if (isset($this->request->data["limit"])) {
            if ((int) $this->request->data["limit"] > 500) {
                $this->request->data["limit"] = 500;
            } elseif ((int) $this->request->data["limit"] < 5) {
                $this->request->data["limit"] = 5;
            }
            $this->limit_clause = (int) $this->request->data["limit"];
            $_SESSION[$this->module]["limit"] = $this->limit_clause;
            $this->page = 1;
        } elseif (isset($_SESSION[$this->module]["limit"])) {
            $this->limit_clause = $_SESSION[$this->module]["limit"];
        }
    }

    /**
     * Build the where clause and params for query
     */
    protected function compileWhereClause()
    {
        // We should only do this once
        if ($this->where_clause) {
            return;
        }
        $this->where_clause = "1=1";
        if (!empty($this->where)) {
            foreach ($this->where as $where_set) {
                $where_clause = array_shift($where_set);
                $this->where_clause .= " AND ({$where_clause})";
                $this->params = [...$this->params, ...$where_set];
            }
        }
    }

    /**
     * Get the table or form columns for query
     */
    protected function getColumns(string $column_type): string
    {
        if ($column_type == "table") {
            $columns = $this->table_columns;
            // We always want the key_col available in the array,
            // so we should aad it if it doesn't exist
            $found = array_filter(
                $columns,
                function ($k) {
                    $k = $this->stripToAlias($k);
                    return $k == $this->key_col ||
                        $k == $this->table . "." . $this->key_col;
                },
                ARRAY_FILTER_USE_KEY
            );
            if (!$found) {
                $columns[$this->table . "." . $this->key_col] = null;
            }
        } elseif ($column_type == "form") {
            $columns = $this->form_columns;
        } else {
            throw new Exception("Unknown column type");
        }
        $columns = array_keys($columns);
        return implode(", ", $columns);
    }

    /**
     * Add a where clause to the main query
     */
    protected function addWhereClause($clause, $params = [])
    {
        $this->where[] = [$clause, ...$params];
    }

    /**
     * Get the table SQL query
     */
    protected function getTableQuery()
    {
        if (!$this->order_by_clause) {
            $this->order_by_clause = $this->key_col;
        }
        $columns = $this->getColumns("table");
        $query = "SELECT {$columns} ";
        $extra_tables = implode(" ", $this->extra_tables);
        $query .= "FROM {$this->table} {$extra_tables} ";
        if (!is_null($this->where_clause)) {
            $query .= "WHERE {$this->where_clause} ";
        }
        if (!is_null($this->group_by_clause)) {
            $query .= "GROUP BY {$this->group_by_clause} ";
        }
        if (!is_null($this->having_clause)) {
            $query .= "HAVING {$this->having_clause} ";
        }
        if (!is_null($this->order_by_clause)) {
            $query .= "ORDER BY {$this->order_by_clause} {$this->sort_clause} ";
        }
        if (!is_null($this->offset_clause)) {
            $query .= "LIMIT {$this->offset_clause}, {$this->limit_clause} ";
        }
        return $query;
    }

    /**
     * Return the SQL query for form view
     */
    protected function getFormQuery()
    {
        $columns = $this->getColumns("form");
        $query = "SELECT {$columns} ";
        $query .= "FROM {$this->table} ";
        if (!is_null($this->where_clause)) {
            $query .= "WHERE {$this->where_clause} ";
        }
        return $query;
    }

    /**
     * Set the results meta and dataset for table output
     */
    protected function getTableData()
    {
        if (!$this->table || empty($this->table_columns)) {
            return;
        }
        // Get the table query
        $query = $this->getTableQuery();
        $stmt = $this->db->run($query, $this->params);
        $this->total_results = $stmt?->rowCount() ?? 0;
        $this->total_pages = ceil($this->total_results / $this->limit_clause);
        if ($this->page > $this->total_pages) {
            $this->page = $this->total_pages;
        }
        if ($this->page < 1) {
            $this->page = 1;
        }
        $this->offset_clause = ($this->page - 1) * $this->limit_clause;
        $this->query = $this->getTableQuery();
        $this->dataset = $this->db
            ->run($this->query, $this->params)
            ?->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Does the user have permission to insert?
     */
    protected function hasInsertPermission()
    {
        return $this->allow_insert;
    }

    /**
     * Does the user have permission to edit?
     */
    protected function hasEditPermission($id)
    {
        return $this->allow_edit;
    }

    /**
     * Does the user have permission to perform table row action?
     */
    protected function hasActionPermission($id)
    {
        return true;
    }

    /**
     * Does the user have permission to delete?
     */
    protected function hasDeletePermission($id)
    {
        return $this->allow_delete;
    }

    /**
     * Override the table row values
     */
    protected function override($context, $datum)
    {
        return $datum;
    }

    /**
     * Format the table column value
     */
    protected function format($column, $value)
    {
        $formatted = null;
        if (isset($this->table_format[$column])) {
            $format_type = $this->table_format[$column];
            if (is_callable($format_type)) {
                $formatted = $format_type($column, $value);
            } else {
                $formatted = match ($format_type) {
                    "pct" => Format::pct($column, $value),
                    "ago" => Format::ago($column, $value),
                    "image" => Format::image($column, $value),
                    "datetime" => Format::dateTime($column, $value),
                    "datetime-local" => Format::dateTimeLocal($column, $value),
                    default => Format::default($column, $value),
                };
            }
        }
        return $formatted ?? "<span>" . $value . "</span>";
    }

    /**
     * Is a function a closure
     */
    private function isClosure($function)
    {
        return $function instanceof Closure;
    }

    /**
     * Control output for form
     */
    protected function control($column, $value)
    {
        $control = null;
        if (isset($this->form_control[$column])) {
            $control_type = $this->form_control[$column];
            if ($this->isClosure($control_type)) {
                $control = $control_type($column, $value);
            } else {
                $control = match ($control_type) {
                    "email" => Control::input($column, $value, "email"),
                    "checkbox" => Control::input($column, $value, "checkbox"),
                    "color" => Control::input($column, $value, "color"),
                    "date" => Control::input($column, $value, "date"),
                    "datetime-local" => Control::input(
                        $column,
                        $value,
                        "datetime-local"
                    ),
                    "file" => Control::input($column, $value, "file"),
                    "hidden" => Control::input($column, $value, "hidden"),
                    "image" => Control::input(
                        $column,
                        "",
                        "image",
                        "src='{$value}'"
                    ),
                    "month" => Control::input($column, $value, "month"),
                    "number" => Control::input($column, $value, "number"),
                    "password" => Control::input($column, $value, "password"),
                    "radio" => Control::input($column, $value, "radio"),
                    "range" => Control::input($column, $value, "range"),
                    "reset" => Control::input($column, $value, "reset"),
                    "search" => Control::input($column, $value, "search"),
                    "submit" => Control::input($column, $value, "submit"),
                    "tel" => Control::input($column, $value, "tel"),
                    "time" => Control::input($column, $value, "time"),
                    "url" => Control::input($column, $value, "url"),
                    "week" => Control::input($column, $value, "week"),
                    "text" => Control::input($column, $value, "text"),
                    "textarea" => Control::textarea($column, $value),
                    default => Control::input($column, $value),
                };
            }
        }
        return $control ?? "<span>" . $value . "</span>";
    }

    /**
     * Set the dataset for form output
     */
    protected function getFormData($id = null)
    {
        if (!$this->table) {
            return;
        }
        if ($id) {
            // Edit an existing record
            $this->query = $this->getFormQuery();
            $data = $this->db
                ->run($this->query, $this->params)
                ?->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($data)) {
                $data = $this->override("form", $data[0]);
                $this->dataset = $data;
            }
        } else {
            // Edit a new record
            $data = [];
            foreach ($this->form_columns as $column => $title) {
                $column = $this->stripToAlias($column);
                $data[$column] = isset($this->form_default[$column])
                    ? $this->form_default[$column]
                    : "";
            }
            $this->dataset = $data;
        }
    }

    /**
     * Build the dataset
     */
    protected function getData($type = "index", $id = null)
    {
        // Process actions requests, etc
        if ($type == "index") {
            $this->processTableRequest();
        } else {
            $this->processFormRequest($id);
        }
        return match ($type) {
            "index" => $this->getTableData(),
            "create" => $this->getFormData(),
            "edit" => $this->getFormData($id),
            "default" => throw new Exception("Unknown data type"),
        };
    }

    /**
     * Strip column to alias name
     */
    protected function stripToAlias($column): string
    {
        $arr = explode(" as ", strtolower($column));
        return end($arr);
    }

    /**
     * Strip table columns to alias name for table output
     */
    protected function fixColumns($columns = "table_columns")
    {
        // Fix up the table_columns array by stripping [0] to alias
        foreach ($this->$columns as $column => $title) {
            $column_raw = $column;
            unset($this->$columns[$column]);
            $this->$columns[$this->stripToAlias($column_raw)] = $title;
        }
    }

    /**
     * Add a requirement to validate
     */
    protected function addValidationRule($column, $rule)
    {
        $idx = array_search($rule, $this->validate[$column]);
        if ($idx === false) {
            $this->validate[$column][] = $rule;
        }
    }

    /**
     * Drop a requirement from validate
     */
    protected function dropValidationRule($column, $rule)
    {
        $idx = array_search($rule, $this->validate[$column]);
        unset($this->validate[$column][$idx]);
        $this->validate[$column] = array_values($this->validate[$column]);
    }

    /**
     * Validation function
     */
    protected function validateRequest(Controller $controller, $id = null)
    {
        $this->fixColumns("form_columns");
        if (!empty($this->request->data)) {
            // Get rid of any columns that aren't defined in form_columns OR
            // are defined in form_exclude array
            foreach ($this->request->data as $column => $value) {
                if (
                    !key_exists($column, $this->form_columns) ||
                    in_array($column, $this->form_exclude)
                ) {
                    unset($this->request->data[$column]);
                }
            }
            if (!empty($this->validate)) {
                // Validate the request data
                $this->dataset = (array) $controller->validateRequest(
                    $this->validate
                );
                $validation_errors = count(Validate::$errors);
                if ($validation_errors) {
                    $msgs = "";
                    foreach (Validate::$errors as $column => $errors) {
                        $title = $this->form_columns[$column];
                        foreach ($errors as $error) {
                            $msg = str_replace($column, $title, $error);
                            $msgs .= "<div>" . $msg . "</div>";
                        }
                    }
                    Flash::addFlash("warning", $msgs);
                }
                return true &&
                    !is_null($this->dataset) &&
                    $validation_errors == 0;
            } else {
                $this->dataset = $this->request->getData();
            }
            return true;
        }
        return false;
    }

    /**
     * Get the table string for output
     */
    protected function getTable()
    {
        $this->fixColumns("table_columns");
        if (!$this->name_col) {
            $this->name_col = $this->key_col;
        }
        ob_start();
        include __DIR__ . "/Table.php";
        $table = ob_get_clean();
        return $table;
    }

    /**
     * Get the form string for output
     */
    protected function getForm($id = null)
    {
        $this->fixColumns("form_columns");
        // Form cancel link
        $cancel = Router::buildRoute("module.index", $this->module);
        // Form action
        $action = $id
            ? Router::buildRoute("module.update", $this->module, $id)
            : Router::buildRoute("module.store", $this->module);
        ob_start();
        include __DIR__ . "/Form.php";
        $form = ob_get_clean();
        return $form;
    }

    /**
     * Return the total count for filter link
     */
    protected function getFilterTotalCount()
    {
        $total = 0;
        // Determine count, up to $limit
        $limit = 500;
        $this->limit_clause = $limit;
        $this->offset_clause = 0;
        $query = $this->getTableQuery();
        $stmt = $this->db->run($query, $this->params);
        $total = $stmt->rowCount();
        // Look for one more
        $this->limit_clause = 1;
        $this->offset_clause = $limit;
        $query = $this->getTableQuery();
        $stmt = $this->db->run($query, $this->params);
        $count = $stmt->rowCount();
        $extra = "";
        // Add a + sign if there is more than $limit records
        if ($count) {
            $extra = "+";
        }
        return $extra . number_format($total);
    }

    /**
     * Are you supposed to be here?
     */
    protected function permissionDenied()
    {
        Flash::addFlash("warning", "Permission denied");
        header("Location: /admin/module/{$this->module}");
        exit();
    }

    /**
     * Audit table changes
     */
    protected static function audit(
        $user_id,
        $table_name,
        $table_id,
        $field,
        $value,
        $message = ""
    ) {
        $db = DB::getInstance();
        $old_value = $db->selectVar(
            "SELECT new_value
                FROM audit
                WHERE table_name = ? AND
                table_id = ? AND
                field = ?
                ORDER BY created_at DESC
                LIMIT 1",
            $table_name,
            $table_id,
            $field
        );
        if ($db->stmt->rowCount() == 0 || $old_value != $value) {
            $db->run(
                "INSERT INTO audit SET
                user_id = ?,
                table_name = ?,
                table_id = ?,
                field = ?,
                old_value = ?,
                new_value = ?,
                message = ?,
                created_at = NOW()",
                [
                    $user_id,
                    $table_name,
                    $table_id,
                    $field,
                    $old_value,
                    $value,
                    $message,
                ]
            );
        }
    }

    /**
     * Does the module exist in the database?
     */
    protected function moduleExists($id)
    {
        return $this->db->selectOne(
            "SELECT {$this->key_col}
                FROM {$this->table}
                WHERE {$this->key_col} = ?",
            $id
        );
    }

    /**
     * Store a new module row in database
     * @return $id
     */
    protected function storeModule()
    {
        $id = null;
        $query = "INSERT INTO {$this->table} SET ";
        $columns = $params = $audit = [];
        foreach ($this->dataset as $column => $value) {
            $columns[] = "{$column} = ?";
            $params[] = $value;
            $audit[] = [$column, $value];
        }
        $query .= implode(", ", $columns);
        $stmt = $this->db->query($query, ...$params);
        $result = $stmt->rowCount() > 0;
        if ($result) {
            $id = $this->db->lastInsertId();
        }
        if ($id && $result) {
            foreach ($audit as $data) {
                list($field, $value) = $data;
                self::audit(
                    $this->user->id,
                    $this->table,
                    $id,
                    $field,
                    $value,
                    "INSERT"
                );
            }
        }
        return $id;
    }

    /**
     * Update an existing module row in database
     */
    protected function updateModule($id)
    {
        if (empty($this->dataset)) {
            return false;
        }
        $query = "UPDATE {$this->table} SET ";
        $columns = $params = $audit = [];
        foreach ($this->dataset as $column => $value) {
            $columns[] = "{$column} = ?";
            $params[] = $value;
            $audit[] = [$column, $value];
        }
        $query .= implode(", ", $columns);
        $query .= " WHERE {$this->key_col} = ?";
        $params[] = $id;
        $stmt = $this->db->query($query, ...$params);
        $result = $stmt->rowCount() > 0;
        if ($result) {
            foreach ($audit as $data) {
                list($field, $value) = $data;
                self::audit(
                    $this->user->id,
                    $this->table,
                    $id,
                    $field,
                    $value,
                    "UPDATE"
                );
            }
        }
        return $result;
    }

    /**
     * Delete an existing module row in database
     */
    protected function deleteModule($id)
    {
        $query = "DELETE FROM {$this->table} ";
        $query .= " WHERE {$this->key_col} = ?";
        $params[] = $id;
        $stmt = $this->db->query($query, ...$params);
        $result = $stmt->rowCount() > 0;
        if ($result) {
            self::audit(
                $this->user->id,
                $this->table,
                $id,
                $this->key_col,
                null,
                "DELETE"
            );
        }
        return $result;
    }

    /**
     * Return data for profiler
     */
    protected function profiler()
    {
        global $global_start;
        if ($_ENV["PROFILER_SHOW"] != "true") {
            return;
        }
        // Profiler
        $slow_traces = [];
        foreach (["Slow DB:" => $this->db->trace_counts] as $title => $traces) {
            //$slow_traces[] = $title;
            if ($traces) {
                uasort($traces, fn($a, $b) => $b["time"] <=> $a["time"]);
                $i = 0;
                foreach ($traces as $key => $value) {
                    $i++;
                    if ($i > 10) {
                        break;
                    }
                    $pct =
                        number_format(
                            ($value["time"] / $this->db->total_time) * 100,
                            2
                        ) . "%";
                    $slow_traces[] = "{$key} &times; {$value["count"]}, {$value["time"]} <strong>{$pct}</strong>";
                }
            }
        }
        return [
            "global_start" => $global_start,
            "total_php" => microtime(true) - $global_start,
            "db_total_time" => $this->db->total_time,
            "db_num_queries" => $this->db->num_queries,
            "slow_traces" => $slow_traces,
        ];
    }

    protected function getSidebarState()
    {
        return isset($_SESSION["sidebar"]) ? $_SESSION["sidebar"] : 1;
    }

    /**
     * Module index route (view)
     */
    public function index(Controller $controller)
    {
        $this->logSession();
        $this->getData("index");
        $table = $this->getTable();
        echo $controller->render("admin/table.html", [
            "table" => $table,
            "title" => $this->title,
            "alerts" => Flash::getSessionFlash(),
            "sidebar_links" => $controller->sidebar_links,
            "profile" => $this->profiler(),
            "sidebar" => $this->getSidebarState(),
        ]);
    }

    /**
     * Module create route (view)
     */
    public function create(Controller $controller)
    {
        $this->logSession();
        if ($this->hasInsertPermission()) {
            $this->getData("create");
            $form = $this->getForm();
            echo $controller->render("admin/form.html", [
                "form" => $form,
                "title" => $this->title,
                "alerts" => Flash::getSessionFlash(),
                "sidebar_links" => $controller->sidebar_links,
                "profile" => $this->profiler(),
                "sidebar" => $this->getSidebarState(),
            ]);
        } else {
            $this->permissionDenied();
        }
    }

    /**
     * Module edit route (view)
     */
    public function edit(Controller $controller, $id)
    {
        $this->logSession();
        if ($this->hasEditPermission($id)) {
            if ($this->moduleExists($id)) {
                $this->getData("edit", $id);
            } else {
                http_response_code(404);
                Flash::addFlash("warning", "Module does not exist");
            }
            $form = $this->getForm($id);
            echo $controller->render("admin/form.html", [
                "form" => $form,
                "title" => $this->title,
                "alerts" => Flash::getSessionFlash(),
                "sidebar_links" => $controller->sidebar_links,
                "profile" => $this->profiler(),
                "sidebar" => $this->getSidebarState(),
            ]);
        } else {
            $this->permissionDenied();
        }
    }

    /**
     * Module store route (POST)
     */
    public function store(Controller $controller)
    {
        $this->logSession();
        if (
            $this->hasInsertPermission() &&
            $this->validateRequest($controller)
        ) {
            // storeModule returns $id, if successful
            $id = $this->storeModule();
            if (!is_null($id)) {
                Flash::addFlash("success", "Module successfully created");
                $this->refreshForm($id);
            } else {
                Flash::addFlash("info", "Could not store the module");
                $this->refreshForm();
            }
        } elseif (!$this->hasInsertPermission()) {
            $this->permissionDenied();
        } else {
            $this->refreshForm();
        }
    }

    /**
     * Module update route (POST/PATCH/PUT)
     */
    public function update(Controller $controller, $id)
    {
        $this->logSession();
        $submit_type = $this->request->data["submit_type"];
        if (
            $this->hasEditPermission($id) &&
            $this->validateRequest($controller, $id)
        ) {
            if ($this->updateModule($id)) {
                Flash::addFlash("success", "Module successfully updated");
                if ($submit_type === "apply") {
                    // Apply, stay in edit view
                    $this->refreshForm($id);
                } else {
                    // Save, go back to table view
                    $this->refreshTable();
                }
            } else {
                Flash::addFlash("info", "No changes were made");
                $this->refreshForm($id);
            }
        } elseif (!$this->hasEditPermission($id)) {
            $this->permissionDenied();
        } else {
            $this->refreshForm($id);
        }
    }

    /**
     * Module destroy route (DELETE)
     */
    public function destroy(Controller $controller, $id)
    {
        $this->logSession();
        if ($this->hasDeletePermission($id)) {
            if ($this->deleteModule($id)) {
                Flash::addFlash("success", "Module successfully deleted");
                $this->refreshTable();
            } else {
                Flash::addFlash("info", "Could not delete the module");
                $this->refreshForm($id);
            }
        } elseif (!$this->hasDeletePermission($id)) {
            $this->permissionDenied();
        } else {
            $this->refreshForm($id);
        }
    }

    /**
     * Get the filter count action (action)
     */
    protected function filterCount()
    {
        // Reset the where and params arrays
        $this->where = $this->params = [];
        $filter = $this->request->data["filter_count"];
        $clause = $this->filter_links[$filter] ?? null;
        if ($clause) {
            $this->addWhereClause($clause);
            $this->handleSearchFilter();
            $this->compileWhereClause();
            $total = $this->getFilterTotalCount();
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(["total" => $total]);
            exit();
        }
    }

    /**
     * Toggle the sidebar state (action)
     */
    protected function toggleSidebar()
    {
        if (!isset($_SESSION["sidebar"])) {
            $_SESSION["sidebar"] = 0;
        } else {
            $_SESSION["sidebar"] = $_SESSION["sidebar"] == 1 ? 0 : 1;
        }
        ob_clean();
        ob_start();
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode(["setting" => $_SESSION["sidebar"]]);
        exit();
    }
}
