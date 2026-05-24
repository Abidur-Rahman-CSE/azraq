<?php

if (PHP_VERSION_ID >= 80100 && ! enum_exists('SortDirection', false)) {
    enum SortDirection
    {
        case Ascending;
        case Descending;
    }
}
