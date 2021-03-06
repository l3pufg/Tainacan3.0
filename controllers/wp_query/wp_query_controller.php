<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * e.g., it puts together the home page when no home.php file exists.
 *
 * Learn more: {@link https://codex.wordpress.org/Template_Hierarchy}
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */
require_once(dirname(__FILE__) . '../../../models/collection/collection_model.php');
require_once(dirname(__FILE__) . '../../../models/wp_query/wp_query_model.php');
require_once(dirname(__FILE__) . '../../general/general_controller.php');

class WPQueryController extends Controller {

    public function operation($operation, $data) {
        $wpquery_model = new WPQueryModel();
         switch ($operation) {
            case "wpquery_select":
                $return = array();
                if(empty($wpquery_model->get_collection_posts($data['collection_id']))){
                    $return['empty_collection'] = true;
                }else{
                    $return['empty_collection'] = false;
                }
                $collection_model = new CollectionModel;
                $args = $wpquery_model->select_filter($data);
                $paramters = $wpquery_model->do_filter($args);
                $data['loop'] =  new WP_Query($paramters);
                $data['collection_data'] = $collection_model->get_collection_data($args['collection_id']);
                $data['listed_by'] = $wpquery_model->get_ordered_name($args['collection_id'], $args['ordenation_id'], $args['order_by']);
                $data['is_moderator'] = CollectionModel::is_moderator($args['collection_id'], get_current_user_id());
                $return['page'] = $this->render(dirname(__FILE__) . '../../../views/object/list.php', $data);
                $return['args'] = serialize($args);
                return json_encode($return);
            case "wpquery_radio":
               $return = array();
                if(empty($wpquery_model->get_collection_posts($data['collection_id']))){
                    $return['empty_collection'] = true;
                }else{
                    $return['empty_collection'] = false;
                }
                $collection_model = new CollectionModel;
                $args = $wpquery_model->radio_filter($data);
                $paramters = $wpquery_model->do_filter($args);
                $data['loop'] =  new WP_Query($paramters);
                $data['collection_data'] = $collection_model->get_collection_data($args['collection_id']);
                $data['listed_by'] = $wpquery_model->get_ordered_name($args['collection_id'], $args['ordenation_id'], $args['order_by']);
                $data['is_moderator'] = CollectionModel::is_moderator($args['collection_id'], get_current_user_id());
                $return['page'] = $this->render(dirname(__FILE__) . '../../../views/object/list.php', $data);
                $return['args'] = serialize($args);
                return json_encode($return);
            case "wpquery_checkbox":
               $return = array();
                if(empty($wpquery_model->get_collection_posts($data['collection_id']))){
                    $return['empty_collection'] = true;
                }else{
                    $return['empty_collection'] = false;
                }
                $collection_model = new CollectionModel;
                $args = $wpquery_model->checkbox_filter($data);
                $paramters = $wpquery_model->do_filter($args);
                $data['loop'] =  new WP_Query($paramters);
                $data['collection_data'] = $collection_model->get_collection_data($args['collection_id']);
                $data['listed_by'] = $wpquery_model->get_ordered_name($args['collection_id'], $args['ordenation_id'], $args['order_by']);
                $data['is_moderator'] = CollectionModel::is_moderator($args['collection_id'], get_current_user_id());
                $return['page'] = $this->render(dirname(__FILE__) . '../../../views/object/list.php', $data);
                $return['args'] = serialize($args);
                return json_encode($return);
            case "wpquery_multipleselect":
                $return = array();
                if(empty($wpquery_model->get_collection_posts($data['collection_id']))){
                    $return['empty_collection'] = true;
                }else{
                    $return['empty_collection'] = false;
                }
                $collection_model = new CollectionModel;
                $args = $wpquery_model->multipleselect_filter($data);
                $paramters = $wpquery_model->do_filter($args);
                $data['loop'] =  new WP_Query($paramters);
                $data['collection_data'] = $collection_model->get_collection_data($args['collection_id']);
                $data['listed_by'] = $wpquery_model->get_ordered_name($args['collection_id'], $args['ordenation_id'], $args['order_by']);
                $data['is_moderator'] = CollectionModel::is_moderator($args['collection_id'], get_current_user_id());
                $return['page'] = $this->render(dirname(__FILE__) . '../../../views/object/list.php', $data);
                $return['args'] = serialize($args);
                return json_encode($return);
            case "wpquery_range":
                $return = array();
                if(empty($wpquery_model->get_collection_posts($data['collection_id']))){
                    $return['empty_collection'] = true;
                }else{
                    $return['empty_collection'] = false;
                }
                $collection_model = new CollectionModel;
                $args = $wpquery_model->range_filter($data);
                $paramters = $wpquery_model->do_filter($args);
                $data['loop'] =  new WP_Query($paramters);
                $data['collection_data'] = $collection_model->get_collection_data($args['collection_id']);
                $data['listed_by'] = $wpquery_model->get_ordered_name($args['collection_id'], $args['ordenation_id'], $args['order_by']);
                $data['is_moderator'] = CollectionModel::is_moderator($args['collection_id'], get_current_user_id());
                $return['page'] = $this->render(dirname(__FILE__) . '../../../views/object/list.php', $data);
                $return['args'] = serialize($args);
                return json_encode($return);
             case "wpquery_fromto":
                $return = array();
                if(empty($wpquery_model->get_collection_posts($data['collection_id']))){
                    $return['empty_collection'] = true;
                }else{
                    $return['empty_collection'] = false;
                }
                $collection_model = new CollectionModel;
                $args = $wpquery_model->fromto_filter($data);
                $paramters = $wpquery_model->do_filter($args);
                $data['loop'] =  new WP_Query($paramters);
                $data['collection_data'] = $collection_model->get_collection_data($args['collection_id']);
                $data['listed_by'] = $wpquery_model->get_ordered_name($args['collection_id'], $args['ordenation_id'], $args['order_by']);
                $data['is_moderator'] = CollectionModel::is_moderator($args['collection_id'], get_current_user_id());
                $return['page'] = $this->render(dirname(__FILE__) . '../../../views/object/list.php', $data);
                $return['args'] = serialize($args);
                return json_encode($return);
            case "wpquery_dynatree":
                $return = array();
                if(empty($wpquery_model->get_collection_posts($data['collection_id']))){
                    $return['empty_collection'] = true;
                }else{
                    $return['empty_collection'] = false;
                }
                $collection_model = new CollectionModel;
                $args = $wpquery_model->dynatree_filter($data);
                $paramters = $wpquery_model->do_filter($args);
                $data['loop'] =  new WP_Query($paramters);
                $data['collection_data'] = $collection_model->get_collection_data($args['collection_id']);
                $data['listed_by'] = $wpquery_model->get_ordered_name($args['collection_id'], $args['ordenation_id'], $args['order_by']);
                $data['is_moderator'] = CollectionModel::is_moderator($args['collection_id'], get_current_user_id());
                $return['page'] = $this->render(dirname(__FILE__) . '../../../views/object/list.php', $data);
                $return['args'] = serialize($args);
                return json_encode($return);
            case "wpquery_menu":
                $return = array();
                $collection_model = new CollectionModel;
                $args = $wpquery_model->dynatree_filter($data);
                $paramters = $wpquery_model->do_filter($args);
                $data['loop'] =  new WP_Query($paramters);
                $data['collection_data'] = $collection_model->get_collection_data($args['collection_id']);
                $data['listed_by'] = $wpquery_model->get_ordered_name($args['collection_id'], $args['ordenation_id'], $args['order_by']);
                $data['is_moderator'] = CollectionModel::is_moderator($args['collection_id'], get_current_user_id());
                $return['page'] = $this->render(dirname(__FILE__) . '../../../views/object/list.php', $data);
                $return['args'] = serialize($args);
                return json_encode($return);
            case "wpquery_ordenation":
                $return = array();
                if(empty($wpquery_model->get_collection_posts($data['collection_id']))){
                    $return['empty_collection'] = true;
                }else{
                    $return['empty_collection'] = false;
                }
                $collection_model = new CollectionModel;
                $args = $wpquery_model->ordenation_filter($data);
                $paramters = $wpquery_model->do_filter($args);
                $data['loop'] =  new WP_Query($paramters);
                $data['collection_data'] = $collection_model->get_collection_data($args['collection_id']);
                $data['listed_by'] = $wpquery_model->get_ordered_name($args['collection_id'], $args['ordenation_id'], $args['order_by']);
                $data['is_moderator'] = CollectionModel::is_moderator($args['collection_id'], get_current_user_id());
                $return['page'] = $this->render(dirname(__FILE__) . '../../../views/object/list.php', $data);
                $return['args'] = serialize($args);
                return json_encode($return);
             case "wpquery_orderby":
                $return = array();
                if(empty($wpquery_model->get_collection_posts($data['collection_id']))){
                    $return['empty_collection'] = true;
                }else{
                    $return['empty_collection'] = false;
                }
                $collection_model = new CollectionModel;
                $args = $wpquery_model->orderby_filter($data);
                $paramters = $wpquery_model->do_filter($args);
                $data['loop'] =  new WP_Query($paramters);
                $data['collection_data'] = $collection_model->get_collection_data($args['collection_id']);
                $data['listed_by'] = $wpquery_model->get_ordered_name($args['collection_id'], $args['ordenation_id'], $args['order_by']);
                $data['is_moderator'] = CollectionModel::is_moderator($args['collection_id'], get_current_user_id());
                $return['page'] = $this->render(dirname(__FILE__) . '../../../views/object/list.php', $data);
                $return['args'] = serialize($args);
                return json_encode($return);
            case "wpquery_page":
                $return = array();
                if(empty($wpquery_model->get_collection_posts($data['collection_id']))){
                    $return['empty_collection'] = true;
                }else{
                    $return['empty_collection'] = false;
                }
                $collection_model = new CollectionModel;
                $args = $wpquery_model->page_filter($data);
                $data['pagid'] = $data['value'];
                $paramters = $wpquery_model->do_filter($args);
                $data['loop'] =  new WP_Query($paramters);
                $data['collection_data'] = $collection_model->get_collection_data($args['collection_id']);
                $data['listed_by'] = $wpquery_model->get_ordered_name($args['collection_id'], $args['ordenation_id'], $args['order_by']);
                $data['is_moderator'] = CollectionModel::is_moderator($args['collection_id'], get_current_user_id());
                $return['page'] = $this->render(dirname(__FILE__) . '../../../views/object/list.php', $data);
                $return['args'] = serialize($args);
                return json_encode($return);
            case "wpquery_keyword":
                $return = array();
                if(empty($wpquery_model->get_collection_posts($data['collection_id']))){
                    $return['empty_collection'] = true;
                }else{
                    $return['empty_collection'] = false;
                }
                $collection_model = new CollectionModel;
                $args = $wpquery_model->keyword_filter($data);
                $paramters = $wpquery_model->do_filter($args);
                $data['loop'] =  new WP_Query($paramters);
                $data['collection_data'] = $collection_model->get_collection_data($args['collection_id']);
                $data['listed_by'] = $wpquery_model->get_ordered_name($args['collection_id'], $args['ordenation_id'], $args['order_by']);
                $data['is_moderator'] = CollectionModel::is_moderator($args['collection_id'], get_current_user_id());
                $return['page'] = $this->render(dirname(__FILE__) . '../../../views/object/list.php', $data);
                $return['args'] = serialize($args);
                return json_encode($return);
            case "filter":
                $return = array();
                $collection_model = new CollectionModel;
                $args = $wpquery_model->filter($data);
                $paramters = $wpquery_model->do_filter($args);
                $data['loop'] =  new WP_Query($paramters);
                $data['collection_data'] = $collection_model->get_collection_data($args['collection_id']);
                $data['listed_by'] = $wpquery_model->get_ordered_name($args['collection_id'], $args['ordenation_id'], $args['order_by']);
                $data['is_moderator'] = CollectionModel::is_moderator($args['collection_id'], get_current_user_id());
                $data['pagid'] = $args['pagid'];
                $return['page'] = $this->render(dirname(__FILE__) . '../../../views/object/list.php', $data);
                $return['args'] = serialize($args);
                if(empty($wpquery_model->get_collection_posts($data['collection_id']))){
                    $return['empty_collection'] = true;
                }else{
                    $return['empty_collection'] = false;
                }
                return json_encode($return);
            case "clean":
                $return = array();
                $collection_model = new CollectionModel;
                $args = $wpquery_model->clean($data);
                $paramters = $wpquery_model->do_filter($args);
                $data['loop'] =  new WP_Query($paramters);
                $data['collection_data'] = $collection_model->get_collection_data($args['collection_id']);
                $data['listed_by'] = $wpquery_model->get_ordered_name($args['collection_id'], $args['ordenation_id'], $args['order_by']);
                $data['is_moderator'] = CollectionModel::is_moderator($args['collection_id'], get_current_user_id());
                $return['page'] = $this->render(dirname(__FILE__) . '../../../views/object/list.php', $data);
                $return['args'] = serialize($args);
                if(empty($wpquery_model->get_collection_posts($data['collection_id']))){
                    $return['empty_collection'] = true;
                }else{
                    $return['empty_collection'] = false;
                }
                return json_encode($return);
            case "remove":
                $return = array();
                if(empty($wpquery_model->get_collection_posts($data['collection_id']))){
                    $return['empty_collection'] = true;
                }else{
                    $return['empty_collection'] = false;
                }
                $collection_model = new CollectionModel;
                $args = $wpquery_model->remove_filter($data);
                $paramters = $wpquery_model->do_filter($args);
                $data['loop'] =  new WP_Query($paramters);
                $data['collection_data'] = $collection_model->get_collection_data($args['collection_id']);
                $data['listed_by'] = $wpquery_model->get_ordered_name($args['collection_id'], $args['ordenation_id'], $args['order_by']);
                $data['is_moderator'] = CollectionModel::is_moderator($args['collection_id'], get_current_user_id());
                $return['page'] = $this->render(dirname(__FILE__) . '../../../views/object/list.php', $data);
                $return['args'] = serialize($args);
                return json_encode($return);
         
         }
        
    }

}

/*
 * Controller execution
 */

if ($_POST['operation']) {
    $operation = $_POST['operation'];
    $data = $_POST;
} else {
    $operation = $_GET['operation'];
    $data = $_GET;
}

$wpquery_controller = new WPQueryController();
echo $wpquery_controller->operation($operation, $data);
?>