<?php
require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');

class mo_user_sync_remote_list extends WP_List_Table
{
    public $record_id_for_bulk_actions = array();

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return array Get the Column headers to be displayed in the table
     */
    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'id' => 'ID',
            'name' => 'Name',
            'url' => 'URL',
            'status' => 'Status'
        );
        return $columns;
    }

    public function prepare_items()
    {
        $current_page = intval($this->get_pagenum());
        $per_page = intval($this->get_items_per_page('servers_per_page', 5));
        $db = new DBUtils;

        $this->process_bulk_action();

        $total_items = intval($db->mo_user_sync_get_row_count());
        if (!empty($_REQUEST['s']) && isset($_REQUEST['s'])) {
            $list_to_array = $this->mo_user_sync_get_remote_list($per_page, $current_page, sanitize_text_field($_REQUEST['s']));
            $total_items = count($list_to_array);
        } else
            $list_to_array = $this->mo_user_sync_get_remote_list($per_page, $current_page);

        $this->set_pagination_args(array(
            'total_items' => $total_items, // total number of items
            'per_page' => $per_page // items to show on a page
        ));

        $this->items = $list_to_array;

    }

    public function process_bulk_action()
    {

        $action = $this->current_action();
        $db = new DBUtils;
        // $form_submission_handler = new form_submission_handler;

        if (isset($_POST['record'])) {
            $record = $_POST['record'];
            array_walk($record, array($this, 'sanitize_record_id_array'));
            switch ($action) {

                case 'Delete':
                    $ids = array_map('absint', $this->record_id_for_bulk_actions);
                    $db->mo_user_sync_bulk_delete($ids);
                    MoUserSyncMessageUtilities::mo_user_sync_show_success_message(MoUserSyncMessageEnums::DELETE);
                    break;

                case 'Activate':
                    $ids = array_map('absint', $this->record_id_for_bulk_actions);
                    $db->mo_user_sync_bulk_activate($ids);
                    MoUserSyncMessageUtilities::mo_user_sync_show_success_message(MoUserSyncMessageEnums::ACTIVATE);
                    break;

                case 'Deactivate':
                    $ids = array_map('absint', $this->record_id_for_bulk_actions);
                    $db->mo_user_sync_bulk_deactivate($ids);
                    MoUserSyncMessageUtilities::mo_user_sync_show_success_message(MoUserSyncMessageEnums::DEACTIVATE);
                    break;

                default:
                    return;
                    break;
            }
        }

        return;
    }

    function mo_user_sync_get_remote_list($per_page = 5, $page_number = 1, $search = '')
    {
        global $wpdb;
        $query = "SELECT `id`,`url`,`status`,`name` FROM `mo_user_sync_remote_server_list`";

        if (!empty($search))
            $query .= " WHERE `id` like '%$search%' OR `url` like '%$search%' OR `status` like '%$search%' OR `name` like '%$search%' ";

        $query .= " LIMIT $per_page";
        $query .= ' OFFSET ' . ($page_number - 1) * $per_page;


        $result = $wpdb->get_results($query, 'ARRAY_A');
        return $result;
    }

    public function get_bulk_actions()
    {

        return array(
            'Activate' => __('Activate', 'WP Remote User Sync'),
            'Deactivate' => __('Deactivate', 'WP Remote User Sync'),
            'Delete' => __('Delete', 'WP Remote User Sync')
        );

    }

    public function sanitize_record_id_array($key, $value)
    {
        array_push($this->record_id_for_bulk_actions, sanitize_text_field($key));
    }

    public function column_id($item)
    {
        $output = '';
        // Title.
        $output .= '<strong><a class="row-title">' . esc_html($item['id']) . '</a></strong>';

        // Get actions.
        $actions = array(
            'edit' => '<a href="?page=wp_to_remote_user_sync&tab=sp_config&attributeMapping=0&remote_server_id=' . esc_attr($item['id']) . '">' . esc_html__('Edit', 'WP Remote User Sync') . '</a>',
            'delete' => '<a style="color:red" href="' . wp_nonce_url('?page=wp_to_remote_user_sync&tab=sp_config&deleted_remote_server=' . esc_attr($item['id'])) . '">' . esc_html__('Delete', 'WP Remote User Sync') . '</a>',
            'attribute mapping' => '<a href="?page=wp_to_remote_user_sync&tab=sp_config&remote_server_id=' . esc_attr($item['id']) . '&attributeMapping=1">' . esc_html__('Attribute Mapping', 'WP Remote User Sync') . '</a>',
        );
        $row_actions = array();

        foreach ($actions as $action => $link) {
            $row_actions[] = '<span class="' . esc_attr($action) . '">' . $link . '</span>';
        }

        $output .= '<div class="row-actions">' . implode(' | ', $row_actions) . '</div>';
        return $output;
    }

    public function no_items()
    {
        _e('No configuration found.');
    }

    protected function single_row_columns($item)
    {
        list($columns, $hidden, $sortable, $primary) = $this->get_column_info();
        foreach ($columns as $column_name => $column_display_name) {
            $classes = "$column_name column-$column_name";
            if ($primary === $column_name) {
                $classes .= ' has-row-actions column-primary';
            }

            if (in_array($column_name, $hidden, true)) {
                $classes .= ' hidden';
            }

            // Comments column uses HTML in the display name with screen reader text.
            // Strip tags to get closer to a user-friendly string.
            $data = 'data-colname="' . esc_attr(wp_strip_all_tags($column_display_name)) . '"';
            if ($column_display_name == 'Status') {
                $attributes = "class='$classes ' $data";
            } else
                $attributes = "class='$classes' $data";

            if ('cb' === $column_name) {
                echo '<th scope="row" class="check-column">';
                echo $this->column_cb($item);
                echo '</th>';
            } elseif (method_exists($this, '_column_' . $column_name)) {
                echo call_user_func(
                    array($this, '_column_' . $column_name),
                    $item,
                    $classes,
                    $data,
                    $primary
                );
            } elseif (method_exists($this, 'column_' . $column_name)) {
                echo "<td $attributes>";
                echo call_user_func(array($this, 'column_' . $column_name), $item);
                echo $this->handle_row_actions($item, $column_name, $primary);
                echo '</td>';
            } else {
                echo "<td $attributes>";
                if ($column_display_name == 'Status' && $item["status"] == "Deactivated") {
                    $table = new DBUtils();
                    $switch = $table->mo_user_sync_get_ids_with_remote_server_id_attribute($item["id"]);
                    echo "<div class='tooltip'>";
                }
                echo $this->column_default($item, $column_name);
                echo $this->handle_row_actions($item, $column_name, $primary);
                if ($column_display_name == 'Status' && $item["status"] == "Deactivated") {
                    if (empty($switch)) {
                        echo "<span class='tooltiptext'>Complete The Required Attribute Mapping</span></div>";
                        //$item["status"] == "Active";
                    } else {
                        echo "<span class='tooltiptext'>The Required Attribute Mapping is complete</span></div>";
                    }
                }
                echo "</td>";
            }
        }
    }

    function column_cb($item)
    {
        return sprintf(

            '<input type="checkbox" name="record[]" value="%d" />', esc_attr($item['id'])
        );

    }

    public function column_default($item, $column_name)
    {
        return $item[$column_name];
    }
}