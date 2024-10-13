<?php

namespace App;

enum UploadProcessStatus: String
{
    case Pending = 'Pending';
    case Processing = 'Processing';
    case Processed = 'Processed';
}
