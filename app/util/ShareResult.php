<?php

namespace app\util;

enum ShareResult
{
    case GRANTED;
    case ALREADY_GRANTED;
    case INVALID_USER;
    case SERVER_ERROR;
}
