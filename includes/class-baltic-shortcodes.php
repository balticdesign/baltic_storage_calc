<?php 

function storage_calc_form() {
    // Start output buffering
    ob_start();

    // Get all terms in the 'rooms' taxonomy
    $terms = get_terms(array(
        'taxonomy'   => 'room',
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'DESC',
    ));
    $optional_rooms = ['office', 'nursery', 'conservatory', 'garage', 'boxes'];
    ?>
    <div id="strg-calc">
    <div class="optional-rooms">
    Add Rooms: 
    <label><input type="checkbox" class="room-toggle" data-room="office">Office</label>
    <label><input type="checkbox" class="room-toggle" data-room="nursery">Nursery</label>
    <label><input type="checkbox" class="room-toggle" data-room="conservatory"> Conservatory</label>
    <label><input type="checkbox" class="room-toggle" data-room="garage"> Garage</label>
    <label><input type="checkbox" class="room-toggle" data-room="boxes">Add Extra Boxes ?</label>
    </div>
    <?php
    foreach ($terms as $term) {
        $additionalClass = in_array($term->slug, $optional_rooms) ? 'optional-room' : '';
        ?>
        <input type="checkbox" id="accordion-<?php echo $term->slug; ?>" class="accordion-toggle">
        <label for="accordion-<?php echo $term->slug; ?>" class="accordion-label <?php echo $additionalClass; ?>"><?php echo $term->name; ?></label>
        <div class="room-section accordion-content" id="room-<?php echo $term->slug; ?>">
            <div class="stg-calc-fields">
        <?php
        // Query items in this room
        // $items = get_posts(array(
        //     'post_type' => 'item',
        //     'numberposts' => -1,
        //     'tax_query' => array(
        //         array(
        //             'taxonomy' => 'room',
        //             'field' => 'slug',
        //             'terms' => $term->slug,
        //             'orderby'    => 'title',
        //             'order'      => 'DESC',
        //         ),
        //     ),
        // ));

        if ($term->slug == 'living-room') {
            // Query for sofas in the living room
            $sofa_items = get_posts(array(
                'post_type' => 'item',
                'numberposts' => -1,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'room',
                        'field' => 'slug',
                        'terms' => $term->slug,
                    ),
                    array(
                        'taxonomy' => 'group',
                        'field' => 'slug',
                        'terms' => 'sofas',
                    ),
                ),
            ));
    
            // Query for other items in the living room
            $other_items = get_posts(array(
                'post_type' => 'item',
                'numberposts' => -1,
                'tax_query' => array(
                    'relation' => 'AND',
                    array(
                        'taxonomy' => 'room',
                        'field' => 'slug',
                        'terms' => $term->slug,
                    ),
                    array(
                        'taxonomy' => 'group',
                        'field' => 'slug',
                        'terms' => 'sofas',
                        'operator' => 'NOT IN',
                    ),
                ),
            ));
    
            // Combine the arrays, with sofas first
            $items = array_merge($sofa_items, $other_items);
        } else {
            // Query for items in other rooms
            $items = get_posts(array(
                'post_type' => 'item',
                'numberposts' => -1,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'room',
                        'field' => 'slug',
                        'terms' => $term->slug,
                    ),
                ),
            ));
        }

        foreach ($items as $item) {
            $volume = get_post_meta($item->ID, 'volume', true);
            $itemId = 'item-' . $item->ID;
            $title = ucwords(strtolower($item->post_title));
            ?>
            <div class="sc-grid-item">
                <label class="item-label" for="item-<?php echo $item->ID; ?>"><?php echo $title; ?></label>
                <div class="number-input-container">
                    <button type="button" onclick="decrementValue('<?php echo $itemId; ?>')">-</button>
                    <input type="number" autocomplete="off" data-lpignore="true" id="item-<?php echo $item->ID; ?>" name="items[<?php echo $item->ID; ?>]" data-volume="<?php echo esc_attr($volume); ?>" data-room="<?php echo $term->slug; ?>" value="0"><br>
                    <button type="button" onclick="incrementValue('<?php echo $itemId; ?>')">+</button>
                </div>
            </div>
            <?php
        }
        ?>
            </div>
        </div>
        <?php
    }
    ?>
        <div id="summaryList">
            <div id="invTog">
                <img src="\wp-content\plugins\baltic_storage_calculator\assets\images\van_vol3.png" />
                <div id="volInv"><span id="vol">0</span> <span>ft&#179;</span></div>
            </div>
            <div id="scrollWrap">
                <table id="summaryTable" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="border: 1px solid black; text-align: left; padding: 8px;">Item</th>
                            <th style="border: 1px solid black; text-align: left; padding: 8px;">Quantity</th>
                            <th style="border: 1px solid black; text-align: left; padding: 8px;">Total Volume</th>
                        </tr>
                    </thead>
                    <tbody id="itemSummary"></tbody>
                    <tfoot>
                        <tr>
                            <td style="border: 1px solid black; text-align: left; padding: 8px; font-weight: bold;" colspan="2">Total Volume</td>
                            <td id="totalVolume" class="center-text" style="border: 1px solid black; padding: 8px;"></td>
                        </tr>
                    </tfoot>
                </table>
                <button id="resetButton" type="button">Reset</button>
                <!--- <input type="submit" value="Submit"> -->
                <div id="close">Close List (X)</div>
            </div>
        </div>
</div>
    <?php

    // End buffering and return the contents
    return ob_get_clean();
}

// Optionally, create a shortcode to place the form in pages or posts
add_shortcode('storage_calc_form', 'storage_calc_form');

add_filter( 'gform_pre_submission_filter_5', 'process_html_table' );
function process_html_table( $form ) {
    foreach ( $form['fields'] as &$field ) {
        $field_id = 4; // replace with the ID of your hidden field
        if ( $field->id != $field_id ) {
            continue;
        }
            $_POST['input_5_4'] = htmlspecialchars_decode( rgpost( 'input_5_4' ) );
        
    }
    return $form;
}

add_filter( 'gform_notification', 'modify_notification_html', 10, 3 );
function modify_notification_html( $notification, $form, $entry ) {
    // if ( $form['id'] != 5 ) {
    //     return $notification;
    // }
    //$testvalue = 'This is a text value';
    $hidden_field_value = rgar( $entry, '4' ); // Corrected to use the proper notation for retrieving the value
    $notification['message'] .= $hidden_field_value; // Append the HTML table to the end of the message

    return $notification;
}