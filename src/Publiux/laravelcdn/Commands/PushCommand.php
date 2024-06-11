<?php

namespace Publiux\laravelcdn\Commands;

use Illuminate\Console\Command;
use Publiux\laravelcdn\Contracts\CdnHelperInterface;
use Publiux\laravelcdn\Contracts\CdnInterface;

/**
 * Class PushCommand.
 *
 * @category Command
 *
 * @author   Mahmoud Zalt <mahmoud@vinelab.com>
 * @author   Raul Ruiz <publiux@gmail.com>
 */
class PushCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'cdn:push {--ver= : The version number to append to the base path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push assets to CDN';

    /**
     * an instance of the main Cdn class.
     *
     * @var Vinelab\Cdn\Cdn
     */
    protected $cdn;

    /**
     * @var CdnHelperInterface
     */
    protected $helper;

    public function __construct(CdnInterface $cdn, CdnHelperInterface $helper)
    {
        $this->cdn    = $cdn;
        $this->helper = $helper;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!empty($this->option('ver'))) {
            $this->cdn->version($this->option('ver'));
        }

        $configurations = $this->helper->getConfigurations();

        if ($this->option('y')) {
            $this->cdn->push();
            return;
        }

        if (!empty($configurations['providers']['aws']['s3']['upload_folder'])) {
            $this->warn(sprintf('Your assets will be uploaded to the following path: "%s"', $configurations['providers']['aws']['s3']['upload_folder']));
        } else {
            $this->warn(sprintf('Your assets will be uploaded to the root of CDN path.'));
        }

        if ($this->confirm('Do you wish to continue?') || $this->option('no-interaction')) {
            $this->cdn->push();
        }
    }
}
