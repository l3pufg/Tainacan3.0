<?php 
/*
 * 
 * View responsavel em mostrar as todas as propriedas para edicao e exclusao no dropdpw da view list
 * 
 * 
 */
include_once ('js/show_list_event_properties_edit_remove_js.php'); ?>
<?php
$has_property = false;
if (isset($property_object)):
    $has_property = true;
    ?>
    <?php
    foreach ($property_object as $property) {
        $object_id = $property['metas']['object_id'];
        ?>  
        <li>&nbsp;&nbsp;<?php echo $property['name']; ?>&nbsp;&nbsp;
            <button onclick="show_edit_object_property_form('<?php echo $object_id ?>','<?php echo $property['id'] ?>')" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-edit"></span></button>&nbsp;
            <button onclick="show_confirmation_delete_property_object_event('<?php echo $object_id ?>','<?php echo $property['id'] ?>','<?php echo $property['name'] ?>')" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-remove"></span></button>
        </li>   
    <?php } ?>
<?php endif; ?>

<?php
if (isset($property_data)):
    $has_property = true;
    foreach ($property_data as $property) {
        $object_id = $property['metas']['object_id'];
        ?>    
       <li>&nbsp;&nbsp;<?php echo $property['name']; ?>&nbsp;&nbsp;
           <button onclick="show_edit_data_property_form('<?php echo $object_id ?>','<?php echo $property['id'] ?>')" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-edit"></span></button>&nbsp;
           <button onclick="show_confirmation_delete_property_data_event('<?php echo $object_id ?>','<?php echo $property['id'] ?>','<?php echo $property['name'] ?>')" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-remove"></span></button>
        </li>          <?php } ?>
            <?php
        endif;
        if (!$has_property) {
            ?>
    <li>&nbsp;&nbsp;<?php echo __('No properties added!','tainacan'); ?></li>   
    <?php
}


