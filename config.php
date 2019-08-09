<?php

class Config {
  public $title = 'Open OnDemand';
  public $nav_background_color = '#53565a';
  public $nav_border_color = '#000';
  public $nav_brand_text_color = '#fff';
  public $change_password_url = null;
}

$config = new Config;


/* OSC Example */
$config->title = 'OSC OnDemand';
$config->nav_background_color = '#6CACE4';
$config->nav_border_color = '#375C84';
$config->change_password_url = 'https://my.osc.edu';


/* Minnesota Supercomputing Center Example */
/* $config->title = 'MSI OnDemand'; */
/* $config->nav_background_color = '#ffd75f'; */
/* $config->nav_border_color = '#000'; */
/* $config->nav_brand_text_color = '#000'; */
?>
