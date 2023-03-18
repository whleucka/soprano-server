<?php

namespace Celestial\Controllers\Admin\Modules;

use Constellation\Controller\Controller;
use Constellation\Module\Module;
use Constellation\View\Control;

class Blog extends Module
{
    public function __construct()
    {
        $this->title = "Blog";
        $this->table = "blog";
        $this->table_columns = [
            "users.name" => "Author",
            "blog.title" => "Title",
            "blog.created_at" => "Created",
            "blog.updated_at" => "Updated",
            "blog.publish_at" => "Published",
        ];
        $this->table_format = [
            "created_at" => "datetime-local",
            "updated_at" => "datetime-local",
            "publish_at" => "datetime-local",
        ];
        $this->form_columns = [
            'title' => 'Title',
            'subtitle' => 'Subtitle',
            'slug' => 'Slug',
            'header_image' => 'Header Image',
            'content' => 'Content',
        ];
        $this->form_control = [
            'title' => 'input',
            'subtitle' => 'input',
            'slug' => 'input',
            'header_image' => 'file',
            'content' => function($column, $value) {
                return Control::editor($column, $value);
            }
        ];
        $this->extra_tables = [
            "INNER JOIN users ON users.id = blog.user_id",
        ];
        $this->table_filters = ["title"];
        parent::__construct("blog");
    }

    protected function validateRequest(Controller $controller, $id = null)
    {
        $_REQUEST['content'] = htmlentities($_REQUEST['content']);
        dump($_REQUEST);
        var_dump($_REQUEST);
        die;
    }
}
