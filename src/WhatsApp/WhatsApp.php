<?php

namespace Zecto\WhatsApp;

use Zecto\Transport;

class WhatsApp
{
    public Messages $messages;
    public Templates $templates;
    public Numbers $numbers;
    public Account $account;
    public Flows $flows;

    public function __construct(Transport $transport)
    {
        $this->messages = new Messages($transport);
        $this->templates = new Templates($transport);
        $this->numbers = new Numbers($transport);
        $this->account = new Account($transport);
        $this->flows = new Flows($transport);
    }
}
