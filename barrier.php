<?php

/**
 * Copyright (c) 2011 Rajesh Kumar <rajesh@meetrajesh.com>
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE. 
 **/

class Barrier {

    private static $_barriers = array();
    private static $_conds = array(); // conditions for each event
    private static $_rmap = array(); // reverse map of conditions to events

    public static function reset() {
        self::$_barriers = array();
        self::$_conds = array();
    }

    // mark a condition as done
    public static function done($cond) {

        // flip the flag
        if (!isset(self::$_barriers[$cond])) {
            self::$_barriers[$cond] = true;
            // find all the events subscribed to a barrier containing this condition
            self::_fire($cond);
        }

    }

    // subscribe to a barrier
    public static function once($conds, $event) {

        // if non-array, cast into array, otherwise do nothing
        $conds = (array)$conds;

        // save the event and its required conditions
        $i = count(self::$_conds); // index of the next new element about to be added
        self::$_conds[$i] = array($event, $conds);
        foreach ($conds as $cond) {
            self::$_rmap[$cond][] = $i;
        }
        
        // check if all conds are already met, if yes, fire the event right away
        if (self::_can_fire($conds)) {
            call_user_function($event);
        }

    }

    // find all the events subscribed to a barrier containing this condition and fire them
    private static function _fire($cond) {
        foreach (self::$_rmap[$cond] as $i) {
            list($event, $conds) = self::$_conds[$i];
            if (self::_can_fire($conds)) {
                call_user_function($event);
            }
        }
    }
    
    // check if all conditions are met
    private static function _can_fire($conds) {
        return count(array_intersect($conds, array_keys(self::$_barriers))) == count($conds);
    }

}

// unit tests
Barrier::once(array('cond1', 'cond2'), function() { echo 'barrier1 is met' . "\n"; });
Barrier::once(array('cond2', 'cond3'), function() { echo 'barrier2 is met' . "\n"; });
Barrier::once(array('cond4', 'cond5'), function() { echo 'barrier3 is met' . "\n"; });

Barrier::done('cond1');
Barrier::done('cond3');
Barrier::done('cond2');