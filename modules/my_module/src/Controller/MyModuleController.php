<?php

namespace Drupal\my_module\Controller;

class MyModuleController{
    public function test(){
        $output = node_load_multiple();
        $output = node_view_multiple($output);
        return array(
            '#markup' => render($output),
        );
    }
}