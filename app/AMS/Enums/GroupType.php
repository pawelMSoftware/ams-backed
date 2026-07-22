<?php

namespace App\AMS\Enums;

/**
 * enum storing Groups types from groups table (Group model)
 */
enum GroupType: int
{
    case EVERYONE = 1;
    case ADMINS_CATEGORY = 4;
    case ADMINS_GROUP = 5;
    case COPYRIGHT_DEPARTMENT = 7;
    case NE_PROJECT = 9;
    case NOWA_ERA = 12;
    case ROYALTY_FREE_EDITORS = 49;
    case SANOMA_UT = 207;
    case ADMINS = 208;
}
