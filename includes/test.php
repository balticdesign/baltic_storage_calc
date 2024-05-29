<?php 

function mbhi_hours() {   
    $currentDate = new DateTime();
    $this_monday = date("Y-M-d", strtotime("this monday"));
    $fri_coming = date("Y-M-d", strtotime("next friday"));
    $thirty_two_days_ahead = date("Y-M-d", strtotime("+32 days"));
	
	$nextMonthDates = array();
	    // Calculate the last day of the following month
    $endOfFollowingMonth = (new DateTime($currentDate->format('Y-M-01')))
        ->add(new DateInterval('P1M'))
        ->modify('last day of this month');
	
    if(date('D') == 'Sat') { $sat_coming = date("Y-M-d"); } else {
    $sat_coming = date("Y-M-d", strtotime("next saturday")); }
    if(date('D') == 'Sun') { $sun_coming = date("Y-M-d"); } else {
    $sun_coming = date("Y-M-d", strtotime("next sunday")); }
    $weekdays = get_field('weekdays');
    $saturday = get_field('saturday');
    $sunday = get_field('sunday');

    $wk_open =  date("H:i", strtotime(get_field('weekdays_wd_store_is_open'))); //$weekdays['wd_store_is_open'];
    $wk_closed = date("H:i", strtotime(get_field('weekdays_wd_store_is_closed')));   //$weekdays['wd_store_is_closed'];


    $sat_open = date("H:i", strtotime(get_field('saturday_sat_store_is_open')));
    $sat_closed = date("H:i", strtotime(get_field('saturday_sat_store_is_closed')));
    $sat_allday = get_field('saturday_sat_closed_all_day');


    $sun_open = date("H:i", strtotime(get_field('sunday_sun_store_is_open')));
    $sun_closed = date("H:i", strtotime(get_field('sunday_sun_store_is_closed')));
    $sun_allday = get_field('sunday_sun_closed_all_day_copy');

    $html = '';
    $html = '<table class="mabel-bhi-businesshours">';

    $html .= '<tbody>';

    $html .= '<tr class="mbhi-is-current"><td class="mabel-bhi-day">Monday - Friday</td><td>'.$wk_open.' - '.$wk_closed.'</td></tr>';

    $sat_html = '';
    $sun_html = '';
    //$html .= '<tr><td class="mabel-bhi-day">Saturday</td><td>'.$sat_open.' - '.$sat_closed.'</td></tr>';
    //$html .= '<tr><td class="mabel-bhi-day">Sunday</td><td>'.$sun_open.' - '.$sun_closed.'</td></tr>';

if( have_rows('holidays') ):

    $weekdays_html = '';

    // Loop through rows.
    while( have_rows('holidays') ) : the_row();

        // Load sub field value.
        $hol_name = get_sub_field('holiday_name');
		$hol_date_from = null;
		$hol_date_to = null;
	
		if(is_int(get_sub_field('ho_date_from'))):
        	$hol_date_from = date("Y-M-d", strtotime(get_sub_field('ho_date_from')));
			$day = date('D', $hol_date_from);
		endif;
		if(get_sub_field('ho_date_to')):
        	$hol_date_to = date("Y-M-d", strtotime(get_sub_field('ho_date_to')));
		endif;
		
	
        // Do something...
        // 
        //Only Check Dates of coming week (could break if we are on weekend) 
         if (($hol_date_from >= $currentDate->format('Y-M-d')) && ($hol_date_to <= $thirty_two_days_ahead)) {
            $weekdays_html .= '<tr><td class="mabel-bhi-day">'.$hol_name.' ('.$day.')</td><td>Closed</td></tr>';
            
    
             // Check if coming Saturday is closed or has a holiday
            if ($sat_allday == '1') { 
                $sat_html = '<tr><td class="nm mabel-bhi-day">Saturday</td><td>Closed</td></tr>'; 
            } 
            elseif ($hol_date_from == $sat_coming ) {
                $sat_html = '<tr><td class="hol mabel-bhi-day">Saturday('.$hol_name.')</td><td>Closed</td></tr>'; 
                $sat_allday = '1';
            } 
  
             // Check if coming sunday is closed or has a holiday
             if ($sun_allday == '1') { 
                $sun_html = '<tr><td class="nm mabel-bhi-day">Sunday</td><td>Closed</td></tr>'; 
            } 
             elseif ( $hol_date_from == $sun_coming ) {
                  $sun_html = '<tr><td class="hol mabel-bhi-day">Sunday</td><td>Closed</td></tr>'; 
                 $sun_allday = '1'; 
             }
            }
    // End loop.
    endwhile;

// No value.
else :
    // Do something...
endif;

//Prints all final dates
if( have_rows('special_dates') ):

    // Loop through rows.
    while( have_rows('special_dates') ) : the_row();

        // Load sub field value.
        $sp_name = get_sub_field('special_hours_name');
        $sp_date = date("Y-M-d", strtotime(get_sub_field('special_hours_date')));
        $spday = date('D', strtotime($sp_date));
        if (get_sub_field('sp_store_is_open') == '') { $sp_open = ''; } else { $sp_open = date("H:i", strtotime(get_sub_field('sp_store_is_open'))); }
        $sp_closed = date("H:i", strtotime(get_sub_field('sp_store_is_closed')));
	
        // Do something...

        // Check if SP Date is within week
 if (($sp_date >= $this_monday) && ($sp_date <= $fri_coming)) {
            // Check if SP Date has opening time
        	if ($sp_open) {  $html .= '<tr><td class="mabel-bhi-day">'.$sp_name.' ('.$spday.')</td><td>'.$sp_open.' - '.$sp_closed.'</td></tr>';  } 
            // SP Date has no opening time, this will trigger a 'Closed' Sign - regular weekdays still shown above
            else {
                $html .= '<tr><td class="mabel-bhi-day">'.$sp_name.' ('.$spday.')</td><td>Closed</td></tr>'; 
			}
        }
        
    // Check if Saturday HTML Already Exists
if ( !$sat_html ) {

        // Check if SP Date is Saturday
            if ($sp_date == $sat_coming ) {
        // Check if SP Date has opening time
                    if ($sp_open) {  
            
                    $sat_html = '<tr><td class="mabel-bhi-day">Saturday ('.$sp_name.')</td><td>'.$sp_open.' - '.$sp_closed.'</td></tr>';   

        // SP Date has no opening time, this will trigger a 'Closed' Sign
                    } else {
                        
                    $sat_html = '<tr><td class="mabel-bhi-day">Saturday('.$sp_name.')</td><td>Closed</td></tr>'; 
                            }

		    } else {
        // Just print Saturday Hours
       if ($sat_allday == '1') { 
            $sat_html = '<tr><td class="nm mabel-bhi-day">Saturday</td><td>Closed</td></tr>'; //--- This never shows up though $sat_allday = '1'
        } else {
        $sat_html .= '<tr><td class="mabel-bhi-day">Saturday</td><td>'.$sat_open.' - '.$sat_closed.'</td></tr>'; }
		} 
    }
    
if ( !$sun_html ) {
        // Check if SP Date is Sunday
        if ($sp_date == $sun_coming ) { 
            // Check if SP Date has opening time
                        if ($sp_open) { 
                            $sun_html = '<tr><td class="mabel-bhi-day">Sunday ('.$sp_name.')</td><td>'.$sp_open.' - '.$sp_closed.'</td></tr>';
                            // SP Date has no opening time, this will trigger a 'Closed' Sign
                        } else {
                            $sun_html = '<tr><td class="mabel-bhi-day">Sunday('.$sp_name.')</td><td>Closed</td></tr>'; 
                                }
		}
       // Just print Sunday Hours
    	else { if ($sun_allday == '1') { 
            $sun_html = '<tr><td class="nm mabel-bhi-day">Sunday</td><td>Closed</td></tr>';   //--- This never shows up though $sun_allday = '1'
        } else {
        $sun_html .= '<tr><td class="mabel-bhi-day">Sunday</td><td>'.$sun_open.' - '.$sun_closed.'</td></tr>';
        }
        }
    }

	if (new DateTime($sp_date) >= $currentDate && new DateTime($sp_date) <= $endOfFollowingMonth) {
 if ($sp_open != '') {
 $nextMonthDates[] = '<tr><td class="mabel-bhi-day t">'.$sp_name.' ('.$spday.')</td><td>'.$sp_open.' - '.$sp_closed.'</td></tr>';
		}  else {
 $nextMonthDates[] = '<tr><td class="mabel-bhi-day">'.$sp_name.' ('.$spday.')</td><td>Closed</td></tr>'; 
			}
}
    // End loop.
    endwhile;
if ((!$sat_html) && (!$sun_html)) {
$sat_html .= '<tr><td class="mabel-bhi-day">Saturday</td><td>'.$sat_open.' - '.$sat_closed.'</td></tr>';
$sun_html .= '<tr><td class="mabel-bhi-day">Sunday</td><td>'.$sun_open.' - '.$sun_closed.'</td></tr>';
}
$html .= $sat_html;
$html .= $sun_html;
$html .= $weekdays_html;
$html .= implode('', $nextMonthDates);	
// No value.
else :
    // Do something...
endif;
	$html .= '</tbody>';
	$html .= '</table>';

return $html;
}
add_shortcode ('mbhi_hours', 'mbhi_hours' );