<?php
/*
Plugin Name: Výstrahy CHMÚ
Plugin URI: https://wordpress.org/plugins-wp/vystrahy-chmu/
Description: Widget pro zobrazení výstrah Českého hydrometeorologického ústavu.
Author: Lukáš Caha
Author URI: http://www.lukascaha.net
Version: 0.1
Text Domain: vystrahy-chmu
*/


/**
 * Add CHMÚ Warnings widget
 */
class CHMU_warnings extends WP_Widget {

	/**
	 * Register widget.
	 */
	function __construct() {
		parent::__construct(
			"chmu-warnings", // Base ID
			"Výstrahy CHMÚ", // Name
			array( "description" => "Výstrahy vydané Českým hydrometeorologickým ústavem" ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 */
	public function widget( $args, $instance ) {
		
		$feedURL = "http://www.chmi.cz/files/portal/docs/meteo/om/zpravy/data/sivs_aktual.xml";
		$sxml = @simplexml_load_file($feedURL);
		$code = $instance['code'];

		foreach( $sxml->country->children() as $reg ) {
			if ( $reg['code']==$code ) { 
				if ( $reg["awareness-level-code"] > 0 ) { 
					
					echo $args['before_widget'];
					
					echo $args['before_title'] . apply_filters( 'widget_title', "Výstrahy pro ".$reg["name"] ). $args['after_title'];
					
					foreach($reg->children() as $sit) {
						echo "<h3 class=\"level_".$sit["awareness-level"]."\">".$sit["awareness-type"]."</h3>";
						echo "<div class=\"textwidget\"><p>platí od ".$sit["start-day"].date(" j.n.Y G.i",strtotime($sit["start-time"]));
						echo " do ".$sit["end-day"].date(" j.n.Y G.i",strtotime($sit["end-time"]));
						if ($sit["districts"]) {
							$districts = str_replace(array(",","JI","PE","HB","TR","ZR"),array(", ","Jihlava","Pelhřimov","Havl. Brod","Třebíč","Žďár n/S"),substr($sit["districts"],0,-1));
							echo " pro okresy ".$districts;
						}
						if ($sit["start-elevation"] AND $sit["start-elevation"]>0) echo " od ".$sit["start-elevation"]."&nbsp;m&nbsp;n.&nbsp;m.";
						if ($sit["end-elevation"] AND $sit["end-elevation"]<1700 ) echo " do ".$sit["end-elevation"]."&nbsp;m&nbsp;n.&nbsp;m.";
						echo "</p>";
					}
					echo "<p><small>";
					
					foreach( $sxml->country->text as $text ) {
						if ( $text['id'] == (string)$sit["awareness-class"] ) {
							foreach( $text->children() as $para ) {
								$list=($para['list']=="true")?" list":"";
								if ($para['type']!="title") echo "<span class=\"".$para['type'].$list."\">".$para."</span><br/>";
							}
						}
					}
										
					echo "</small></p>";
					echo "<p align=\"right\"><small>Zdroj: CHMÚ</small></p></div>";
					
					echo $args['after_widget'];
				} 
	   		} 
		}
	}


	/**
	 * Back-end widget form.
	 */
	public function form( $instance ) {
		
		$code = $instance['code'];
		
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'code' ); ?>">Výstrahy pro</label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'code' ); ?>" name="<?php echo $this->get_field_name( 'code' ); ?>" value="<?php echo esc_attr( $code ); ?>">
				<option value="A" <?php if ($code == "A") echo "selected=\"selected\""; ?>>Praha</option>
				<option value="S" <?php if ($code == "S") echo "selected=\"selected\""; ?>>Středočeský kraj</option>
				<option value="K" <?php if ($code == "K") echo "selected=\"selected\""; ?>>Karlovarský kraj</option>
				<option value="P" <?php if ($code == "P") echo "selected=\"selected\""; ?>>Plzeňský kraj</option>
				<option value="C" <?php if ($code == "C") echo "selected=\"selected\""; ?>>Jihočeský kraj</option>
				<option value="E" <?php if ($code == "E") echo "selected=\"selected\""; ?>>Pardubický kraj</option>
				<option value="H" <?php if ($code == "H") echo "selected=\"selected\""; ?>>Královéhradecký kraj</option>
				<option value="L" <?php if ($code == "L") echo "selected=\"selected\""; ?>>Liberecký kraj</option>
				<option value="U" <?php if ($code == "U") echo "selected=\"selected\""; ?>>Ústecký kraj</option>
				<option value="J" <?php if ($code == "J") echo "selected=\"selected\""; ?>>Kraj Vysočina</option>
				<option value="B" <?php if ($code == "B") echo "selected=\"selected\""; ?>>Jihomoravský kraj</option>
				<option value="Z" <?php if ($code == "Z") echo "selected=\"selected\""; ?>>Zlínský kraj</option>
				<option value="M" <?php if ($code == "M") echo "selected=\"selected\""; ?>>Olomoucký kraj</option>
				<option value="T" <?php if ($code == "T") echo "selected=\"selected\""; ?>>Moravskoslezský kraj</option>
			</select>
		</p>
		<?php

	}

	/**
	 * Sanitize widget form values as they are saved.
	 */
	public function update( $new_instance, $old_instance ) {
		
		$instance = array();
		$instance['code'] = $new_instance['code'];
		
		return $instance;
		
	}

} // class CHMU_warnings

function register_chmu_warnings() {
    register_widget( 'CHMU_warnings' );
}
add_action( 'widgets_init', 'register_chmu_warnings' );

?>