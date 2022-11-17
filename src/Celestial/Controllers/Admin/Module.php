<?php

namespace Celestial\Controllers\Admin;

use Constellation\Alerts\Flash;
use Constellation\Authentication\Auth;
use PDO;
use Constellation\Controller\Controller;
use Constellation\Database\DB;
use Constellation\Http\Request;
use Constellation\View\Format;
use Exception;

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
    protected int $total_pages = 0;
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
    protected int $limit_clause = 10;
    // Table columns
    protected array $table_columns = [];
    // Form columns
    protected array $form_columns = [];
    // Show table actions cell
    protected bool $show_table_actions = true;
    // Table filters (search, date, etc)
    protected array $table_filters = [];
    // Table formatting
    protected array $table_format = [];
    // Table filter links
    protected array $filter_links = [];
    // Table actions
    private array $table_actions = [];

    public function __construct(public ?string $module = null)
    {
        // Current user
        $this->user = Auth::user();
        // Get database instance
        $this->db = DB::getInstance();
        // Get request
        $this->request = Request::getInstance();
    }

    protected function getCurrentUrl()
    {
        return (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on"
            ? "https"
            : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }

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

    protected function processRequest()
    {
        $this->handleSettings();
        $this->handleFilters();
        $this->handleActions();
        $this->compileWhereClause();
    }

    protected function refreshTable()
    {
        $get_params = "?page={$this->page}";
        header("Location: {$get_params}");
        exit();
    }

    protected function refreshForm()
    {
    }

    /**
     * Handle page number, sort, limit clause, etc
     */
    protected function handleSettings()
    {
        $this->setPage();
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
     * Handle page actions
     */
    protected function handleActions()
    {
        if (isset($this->request->data["a"])) {
            $this->action($this->request->data["a"]);
        }
    }

    protected function action($action)
    {
        switch ($action) {
            case "cancel":
                Flash::addFlash("info", "Action cancelled");
                $this->refreshTable();
                break;
            case "filter_count":
                $this->filterCount();
                break;
            default:
                Flash::addFlash("error", "Unknown action");
                $this->refreshTable();
        }
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
     * Set the limit (rows per page)
     */
    protected function setLimit()
    {
        if (isset($this->request->data["limit"])) {
            $this->limit_clause = (int) $this->request->data["limit"];
            $_SESSION[$this->module]["limit"] =
                $this->limit_clause;
            $this->page = 1;
        } elseif (isset($_SESSION[$this->module]["limit"])) {
            $this->limit_clause =
                $_SESSION[$this->module]["limit"];
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
            if (!$columns) {
                // Auto-fill columns and show them all
                // if table_columns is not defined
                $table_data = $this->db->selectMany("SHOW columns
                FROM {$this->table}");
                foreach ($table_data as $info) {
                    $this->table_columns[$info->Field] = $info->Field;
                }
                $columns = $this->table_columns;
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
        $columns = $this->getColumns("table");
        $query = "SELECT {$columns} ";
        $extra_tables = implode(" ", $this->extra_tables);
        $query .= "FROM {$this->table} {$extra_tables}";
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
     * Set the results meta and dataset for table output
     */
    protected function getTableData()
    {
        if (!$this->table) {
            return;
        }
        // Process actions requests, etc
        $this->processRequest();
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
     * Does the user have permission to delete?
     */
    protected function hasDeletePermission($id)
    {
        return $this->allow_delete;
    }

    /**
     * Override the table row values
     */
    protected function override(&$datum)
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
                    "text" => Format::default($column, $value),
                    "pct" => Format::pct($column, $value),
                    "ago" => Format::ago($column, $value),
                };
            }
        }
        return $formatted ?? $value;
    }

    /**
     * Set the dataset for form output
     */
    protected function getFormData($id)
    {
    }

    /**
     * Build the dataset
     */
    protected function getData($type = "index", $id = null)
    {
        return match ($type) {
            "index" => $this->getTableData(),
            "create" => $this->getFormData($id),
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
    protected function fixColumns()
    {
        // Fix up the table_columns array by stripping [0] to alias
        foreach ($this->table_columns as $column => $title) {
            $column_raw = $column;
            unset($this->table_columns[$column]);
            $this->table_columns[$this->stripToAlias($column_raw)] = $title;
        }
    }

    /**
     * Get the table string for output
     */
    protected function getTable()
    {
        $this->fixColumns();
        if (!$this->name_col) {
            $this->name_col = $this->key_col;
        }
        if (!$this->order_by_clause) {
            $this->order_by_clause = $this->key_col;
        }
        ob_start();
        include __DIR__ . "/Table.php";
        $table = ob_get_clean();
        return $table;
    }

    /**
     * Get the form string for output
     */
    protected function getForm()
    {
        ob_start();
        include __DIR__ . "/Form.php";
        $form = ob_get_clean();
        return $form;
    }

    protected function permissionDenied()
    {
        Flash::addFlash("warning", "Permission denied");
        header("Location: /admin/module/{$this->module}");
        exit();
    }

    /**
     * Module index route
     */
    public function index(Controller $controller)
    {
        $this->logSession();
        $this->getData("index");
        $table = $this->getTable();
        echo $controller->render("admin/table.html", [
            "table" => $table,
            "sidebar_links" => $controller->sidebar_links,
        ]);
    }

    /**
     * Module create route
     */
    public function create(Controller $controller)
    {
        $this->logSession();
        if ($this->hasInsertPermission()) {
            $this->getData("create");
            $form = $this->getForm();
            echo $controller->render("admin/form.html", [
                "form" => $form,
                "sidebar_links" => $controller->sidebar_links,
            ]);
        } else {
            $this->permissionDenied();
        }
    }

    /**
     * Module edit route
     */
    public function edit(Controller $controller, $id)
    {
        $this->logSession();
        if ($this->hasEditPermission($id)) {
            $this->getData("edit", $id);
            $form = $this->getForm();
            echo $controller->render("admin/form.html", [
                "form" => $form,
                "sidebar_links" => $controller->sidebar_links,
            ]);
        } else {
            $this->permissionDenied();
        }
    }

    /**
     * Module store route
     */
    public function store(Controller $controller)
    {
        $this->logSession();
        if ($this->hasInsertPermission()) {
            echo "store() WIP";
        } else {
            $this->permissionDenied();
        }
    }

    /**
     * Module update route
     */
    public function update(Controller $controller, $id)
    {
        $this->logSession();
        if ($this->hasEditPermission($id)) {
            echo "update() WIP";
        } else {
            $this->permissionDenied();
        }
    }

    /**
     * Module destroy route
     */
    public function destroy(Controller $controller, $id)
    {
        $this->logSession();
        if ($this->hasDeletePermission($id)) {
            echo "destroy() WIP";
        } else {
            $this->permissionDenied();
        }
    }

    /**
     * Get the filter count
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
            $total = $this->getTotalCount();
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(["total" => $total]);
            exit();
        }
    }

    protected function getTotalCount()
    {
        $total = 0;
        // Determine count, up to 1000
        $limit = 1000;
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
        // Add a + sign if there is more than 1000 records
        if ($count) {
            $total .= "+";
        }
        return $total;
    }
}
