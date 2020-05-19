<?php

use SilverStripe\Dev\BuildTask;

class DevTaskFlush extends BuildTask {
    public $description = "call with flush=1";
    public function run($request) {
      if (!array_get($_GET,'flush')) {
        echo "⚠ NOT flushed";
      } else {
        echo "✔ flushed";
      }
      echo "\n\n";
    }
}
