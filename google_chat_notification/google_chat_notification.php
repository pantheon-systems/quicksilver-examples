<?php

// Run Google Chat notification.
new GoogleChatNotification();

class GoogleChatNotification
{
    // Build a set of fields to be rendered with Google Chat.
    // https://developers.google.com/chat/api/reference/rest/v1/spaces.messages
    public $webhook_url;
    public $secrets;
    public $site_name;
    public $site_env;
    public $site_id;
    public $user_email;
    public $user_fullname;
    public $workflow_type;
    public $workflow_description;
    public $workflow_id;
    public $workflow_stage;
    public $quicksilver_description;
    public $workflow_label;
    public $workflow;
    public $environment_link;
    public $dashboard_link;

    public function __construct() {
        // Ensure we're in Pantheon context.
        if ($this->isPantheon() && $this->isQuicksilver()) {
            $this->webhook_url = $this->getSecret('google_chat_webhook');
            $this->setQuicksilverVariables();

            // Get Workflow message
            $data = $this->prepareOutputByWorkflow($this->workflow_type);
            $this->send($data);
        }
    }

    /**
     * Get the Pantheon site name.
     * @return string|null
     */
    public function getPantheonSiteName(): ?string
    {
        return !empty($_ENV['PANTHEON_SITE_NAME']) ? $_ENV['PANTHEON_SITE_NAME'] : NULL;
    }

    /**
     * Get the Pantheon site id.
     * @return string|null
     */
    public function getPantheonSiteId(): ?string
    {
        return !empty($_ENV['PANTHEON_SITE']) ? $_ENV['PANTHEON_SITE'] : NULL;
    }

    /**
     * Get the Pantheon environment.
     * @return string|null
     */
    public function getPantheonEnvironment(): ?string
    {
        return !empty($_ENV['PANTHEON_ENVIRONMENT']) ? $_ENV['PANTHEON_ENVIRONMENT'] : NULL;
    }

    /**
     * Check if in the Pantheon site context.
     * @return bool|void
     */
    public function isPantheon() {
        if ($this->getPantheonSiteName() !== NULL && $this->getPantheonEnvironment() !== NULL) {
            return TRUE;
        }
        die('No Pantheon environment detected.');
    }

    /**
     * Check if in the Quicksilver context.
     * @return bool|void
     */
    public function isQuicksilver() {
        if ($this->isPantheon() && !empty($_POST['wf_type'])) {
            return TRUE;
        }
        die('No Pantheon Quicksilver environment detected.');
    }

    /**
     * Set Quicksilver variables from POST data.
     * @return void
     */
    public function setQuicksilverVariables() {
        $this->site_name = $this->getPantheonSiteName();
        $this->site_id = $this->getPantheonSiteId();
        $this->site_env = $this->getPantheonEnvironment();
        $this->user_fullname = $_POST['user_fullname'];
        $this->user_email = $_POST['user_email'];
        $this->workflow_id = $_POST['trace_id'];
        $this->workflow_description = $_POST['wf_description'];
        $this->workflow_type = $_POST['wf_type'];
        $this->workflow_stage = $_POST['stage'];
        $this->workflow = ucfirst($this->workflow_stage) . ' ' . str_replace('_', ' ', $this->workflow_type);
        $this->workflow_label = "Quicksilver workflow";
        $this->quicksilver_description = $_POST['qs_description'];
        $this->environment_link = "https://$this->site_env-$this->site_name.pantheonsite.io";
        $this->dashboard_link = "https://dashboard.pantheon.io/sites/$this->site_id#$this->site_env";
    }

    /**
     * Load secrets from secrets file.
     */
    public function getSecrets()
    {
        if (empty($this->secrets)) {
            $secretsFile = $_ENV['HOME'] . 'files/private/secrets.json';
            if (!file_exists($secretsFile)) {
                die('No secrets file found. Aborting!');
            }
            $secretsContents = file_get_contents($secretsFile);
            $secrets = json_decode($secretsContents, TRUE);
            if (!$secrets) {
                die('Could not parse json in secrets file. Aborting!');
            }
            $this->secrets = $secrets;
        }
        return $this->secrets;
    }

    /**
     * @param string $key Key in secrets that must exist.
     * @return mixed|void
     */
    public function getSecret(string $key) {
        $secrets = $this->getSecrets();
        $missing = array_diff([$key], array_keys($secrets));
        if (!empty($missing)) {
            die('Missing required keys in json secrets file: ' . implode(',', $missing) . '. Aborting!');
        }
        return $secrets[$key];
    }

    /**
     * @param string $workflow
     * @return array|null|string
     */
    public function prepareOutputByWorkflow(string $workflow): ?array
    {
        switch ($workflow) {
            case 'sync_code':
                $this->workflow_label = "Sync code";
                $output = $this->syncCodeOutput();
                break;
            case 'deploy_product':
                $this->workflow_label = "Create new site";
                $output = $this->deployProductOutput();
                break;
            case 'deploy':
                $this->workflow_label = "Code or data deploys targeting an environment";
                $output = $this->deployOutput();
                break;
            case 'create_cloud_development_environment':
                $this->workflow_label = "Create multidev environment";
                $output = $this->createMultidevOutput();
                break;
            case 'clone_database':
                $this->workflow_label = "Clone database and files";
                $output = $this->cloneDatabaseOutput();
                break;
            case 'clear_cache':
                $this->workflow_label = "Clear site cache";
                $output = $this->clearCacheOutput();
                break;
            case 'autopilot_vrt':
                $this->workflow_label = "Autopilot visual regression test";
                $output = $this->autopilotVrtOutput();
                break;
            default:
                $output = $this->defaultOutput();
                break;
        }

        return $output;
    }

    /**
     * @param array $cards
     * @return array[]
     */
    public function createCardPayload(array $cards): array
    {
        return ['cards_v2' => [(object) $cards]];
    }

    /**
     * @param string $id
     * @return array
     */
    public function createCardTemplate(string $id): array
    {
        return [
            'card_id' => $id,
            'card' => [],
        ];
    }

    /**
     * @param array $buttons
     * @return array[][]
     */
    public function createCardButtonList(array $buttons): array
    {
        return [
            'buttonList' => [
                'buttons' => $buttons,
            ],
        ];
    }

    /**
     * @param $text
     * @param $url
     * @return array
     */
    public function createCardButton($text, $url): array
    {
        return [
            'text' => $text,
            'onClick' => [
                'openLink' => [
                    'url' => $url,
                ],
            ],
        ];
    }

    /**
     * Create common buttons for different workflows.
     * @return array
     */
    public function createCommonButtons(): array
    {
        $dashboard_button = $this->createCardButton('View Dashboard', $this->dashboard_link);
        $environment_button = $this->createCardButton('View Site Environment', $this->environment_link);
        return [$dashboard_button, $environment_button];
    }

    /**
     * @return array[] Divider element.
     */
    public function createDivider(): array
    {
        return [ 'divider' => (object) array() ];
    }

    /**
     * @param string $text
     * @return string[][]
     */
    public function createDecoratedText(string $text): array
    {
        return [
            'decoratedText' => [
                'text' => $text,
            ]
        ];
    }

    /**
     * @param $title
     * @param $subtitle
     * @param string $image_url
     * @param string $image_type
     * @return array
     */
    public function createCardHeader($title, $subtitle = null, string $image_url = "https://avatars.githubusercontent.com/u/88005016", string $image_type = "CIRCLE" ): array
    {
        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'imageUrl' => $image_url,
            'imageType' => $image_type,
        ];
    }

    /**
     * @param string $messageText
     * @param array $buttons
     * @return array
     */
    public function prepareCardOutput(string $messageText, array $buttons = []): array
    {
        $cardTemplate = $this->createCardTemplate($this->workflow_id);
        $cardTemplate['card']['header'] = $this->createCardHeader($this->quicksilver_description, $this->workflow_label);

        // Create card widgets.
        $widgets = [];
        $widgets[] = $this->createDecoratedText(trim($messageText));
        if (!empty($buttons)) {
            $widgets[] = $this->createDivider();
            $widgets[] = $this->createCardButtonList($buttons);
        }

        $cardTemplate['card']['sections'] = ['widgets' => $widgets];

        return $cardTemplate;
    }

    /**
     * @param array $post
     * @return void
     */
    public function send(array $post) {

        $payload = json_encode($post, JSON_PRETTY_PRINT);

        print_r($payload);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->webhook_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        // Watch for messages with `terminus workflows watch --site=SITENAME`
        print("\n==== Posting to Google Chat ====\n");
        $result = curl_exec($ch);
        print("RESULT: $result");
        // $payload_pretty = json_encode($post,JSON_PRETTY_PRINT); // Uncomment to debug JSON
        // print("JSON: $payload_pretty"); // Uncomment to Debug JSON
        print("\n===== Post Complete! =====\n");
        curl_close($ch);
    }

    /**
     * Get output from shell commands.
     * @param $cmd
     * @return string
     */
    public function getCommandOutput($cmd): string
    {
        $command = escapeshellcmd($cmd);
        return trim(shell_exec($command));
    }

    /**
     * Generate message for sync_code workflow.
     * @return array[]
     */
    public function syncCodeOutput(): array
    {
        // Get the committer, hash, and message for the most recent commit.
        $committer = $this->getCommandOutput('git log -1 --pretty=%cn');
        $email = $this->getCommandOutput('git log -1 --pretty=%ce');
        $message = $this->getCommandOutput('git log -1 --pretty=%B');
        $hash = $this->getCommandOutput('git log -1 --pretty=%h');

        $text = <<<MSG
        <b>Committer:</b> $committer ($email)
        <b>Commit:</b> $hash
        <b>Commit Message:</b> $message
MSG;

        $card = $this->prepareCardOutput($text, $this->createCommonButtons());
        return $this->createCardPayload($card);

    }

    /**
     * Generate message for deploy_product workflow.
     * @return array[]|object[][]
     */
    public function deployProductOutput(): array
    {
        $text = <<<MSG
        New site created!
        <b>Site name:</b> $this->site_name
        <b>Created by:</b> $this->user_email
MSG;

        $card = $this->prepareCardOutput($text, $this->createCommonButtons());
        return $this->createCardPayload($card);
    }

    /**
     * Generate message for deploy workflow.
     * @return array[]|object[][]
     */
    public function deployOutput(): array
    {
        // Find out what tag we are on and get the annotation.
        $deploy_tag = $this->getCommandOutput('git describe --tags');
        $deploy_message = $_POST['deploy_message'];

        $text = <<<MSG
        Deploy to the $this->site_env environment of $this->site_name by $this->user_email is complete!
        
        <b>Deploy Tag:</b> $deploy_tag
        <b>Deploy Note:</b> $deploy_message
MSG;

        $card = $this->prepareCardOutput($text, $this->createCommonButtons());
        return $this->createCardPayload($card);
    }

    /**
     * Generate message for create_cloud_development_environment workflow.
     * @return array[]|object[][]
     */
    public function createMultidevOutput(): array
    {
        $text = <<<MSG
        New multidev environment created!
        <b>Environment name:</b> $this->site_env
        <b>Created by:</b> $this->user_email
MSG;

        $card = $this->prepareCardOutput($text, $this->createCommonButtons());
        return $this->createCardPayload($card);
    }

    /**
     * Generate message for clone_database workflow.
     * @return array[]|object[][]
     */
    public function cloneDatabaseOutput(): array
    {
        $to = $_POST['to_environment'];
        $from = $_POST['from_environment'];
        $text = <<<MSG
        <b>From environment:</b> $from
        <b>To environment:</b> $to
        <b>Started by:</b> $this->user_email
MSG;

        $card = $this->prepareCardOutput($text, $this->createCommonButtons());
        return $this->createCardPayload($card);
    }

    /**
     * Generate message for clear_cache workflow.
     * @return array[]|object[][]
     */
    public function clearCacheOutput(): array
    {
        $text = "Cleared caches on the $this->site_env environment of $this->site_name!";
        $card = $this->prepareCardOutput($text);
        return $this->createCardPayload($card);
    }

    /**
     * Generate message for autopilot_vrt workflow.
     * @return array[]|object[][]
     */
    public function autopilotVrtOutput(): array
    {
        $status = $_POST['vrt_status'];
        $result_url = $_POST['vrt_result_url'];
        $updates_info = $_POST['updates_info'];
        $text = <<<MSG
        <b>Status:</b> $status
        <b>Report:</b> <a href="$result_url">View VRT Report</a>
        <b>Started by:</b> $this->user_email
MSG;

        $card = $this->prepareCardOutput($text, $this->createCommonButtons());
        return $this->createCardPayload($card);
    }

    /**
     * Generate default output for undefined workflows.
     * @return array
     */
    public function defaultOutput(): array
    {
        return ['text' => $this->quicksilver_description ];
    }

}
