<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?>
    <?php if(isset($breadcrumb['href'])) { ?>
    <a href="<?php echo $breadcrumb['href']; ?>">
    <?php } ?>
    <?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php foreach($errors as $error): ?>
  <div class="warning"><?php echo $error; ?></div>
  <?php endforeach; ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/payment.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
          <a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a>
          <a href="<?php echo $cancel; ?>" class="button"><?php echo $button_cancel; ?></a>
      </div>
    </div>
    <div class="content">
      <form action="" method="POST" enctype="multipart/form-data" id="form">
        <table class="form">
          <tr>
            <td><?php echo $entry_total; ?></td>
            <td><input type="text" name="required_total" value="<?php echo $required_total; ?>" /></td>
          </tr>
          <tr>
            <td><?php echo $entry_order_status; ?></td>
            <td><select name="order_status">
                <?php foreach ($order_statuses as $option) { ?>
                <?php if ($option['order_status_id'] == $order_status) { ?>
                <option value="<?php echo $option['order_status_id']; ?>" selected="selected">
                <?php echo $option['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $option['order_status_id']; ?>">
                <?php echo $option['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
          </tr>
          <tr>
            <td><?php echo $entry_geo_zone; ?></td>
            <td><select name="geo_zone">
                <option value="0"><?php echo $text_all_zones; ?></option>
                <?php foreach ($geo_zones as $option) { ?>
                <?php if ($option['geo_zone_id'] == $geo_zone) { ?>
                <option value="<?php echo $option['geo_zone_id']; ?>" selected="selected">
                <?php echo $option['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $option['geo_zone_id']; ?>"><?php echo $option['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
          </tr>
          <tr>
            <td><?php echo $entry_status; ?></td>
            <td><select name="status">
                <?php if ($status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select></td>
          </tr>
          <tr>
            <td><?php echo $entry_sort_order; ?></td>
            <td><input type="text" name="sort_order" value="<?php echo $sort_order; ?>" size="1" /></td>
          </tr>
        </table>
      </form>
    </div>
  </div>
</div>
<?php echo $footer; ?>