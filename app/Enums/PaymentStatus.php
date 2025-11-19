<?php

namespace App\Enums;

enum PaymentStatus: string
{
  case PENDING = 'pending';        
  case INIT_FAILED = 'init_failed';    
  case INIT_UNKNOWN = 'init_unknown';   
  case VERIFY_PENDING = 'verify_pending'; 
  case SUCCESS = 'success';
  case FAILED = 'failed';
}
