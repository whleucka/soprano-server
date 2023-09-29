<?php

namespace Celestial\Controllers\Admin\Modules;

use Constellation\Alerts\Flash;
use Constellation\Authentication\Auth;
use Constellation\Module\Module;
use Celestial\Models\Audit as AuditModel;

class Audit extends Module
{
    public function __construct()
    {
        $this->user = Auth::user();
        $this->allow_insert = $this->allow_edit = $this->allow_delete = false;
        $this->table = "audit";
        $this->title = "Audit";
        $this->table_columns = [
            "audit.id" => "ID",
            "users.name" => "User",
            "table_name" => "Table",
            "table_id" => "ID",
            "field" => "Field",
            "old_value" => "Old Value",
            "new_value" => "New Value",
            "message" => "Message",
            "audit.created_at" => "Created",
        ];
        $this->table_format = [
            "created_at" => "datetime-local",
            "old_value" => "show-more",
            "new_value" => "show-more",
        ];
        $this->extra_tables = ["INNER JOIN users ON users.id = user_id"];
        $this->table_filters = [
            "field",
            "old_value",
            "new_value",
            "name",
            "message",
        ];
        $this->order_by_clause = "audit.created_at";
        $this->sort_clause = "DESC";
        $this->filter_links = [
            "Me" => "user_id = " . $this->user?->id,
            "Others" => "user_id != " . $this->user?->id,
        ];
        $this->addTableAction('undo', "Undo", confirmation: "Are you sure you want to revert to the old value?");
        parent::__construct("audit");
    }

    protected function processTableAction(string $action): void
    {
        $id = intval($_REQUEST['id']);
        $record = $this->db->selectOne("SELECT * FROM audit WHERE id = ?", $id);
        if ($record) {
            if (in_array($record->message, ["UNDO", "UPDATE"])) {
                $exists = $this->db->selectOne("SELECT * FROM {$record->table_name} WHERE id = ?", $record->table_id);
                if ($exists) {
                    $result = $this->db->query("UPDATE {$record->table_name} SET {$record->field} = ? WHERE id = ?", $record->old_value, $record->table_id);
                    if ($result) {
                        $this->audit($this->user?->id, $record->table_name, $record->table_id, $record->field, $record->old_value, "UNDO");
                    } else {
                        Flash::addFlash("danger", "Undo failed.");
                    }
                } else {
                    Flash::addFlash("info", "This record no longer exists.");
                }
            } else {
                Flash::addFlash("info", "Undo action can only be triggered on records where message = UPDATE or UNDO");
            }
        }
    }

    protected function hasActionPermission($id): bool
    {
        $record = AuditModel::find([$id]);
        return in_array($record->message, ["UNDO", "UPDATE"]);
    }
}
