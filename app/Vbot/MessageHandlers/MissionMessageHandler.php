<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2017-08
 */

namespace App\Vbot\MessageHandler;

use App\Models\ApiGroup;
use App\Vbot\MessageAnswers\ExecuteTesting;
use App\Vbot\MessageAnswers\MessageAnswerInterface;
use App\Vbot\MessageAnswers\QueryApiGroup;
use App\Vbot\MessageAnswers\QueryMission;
use Hanson\Vbot\Extension\AbstractMessageHandler;
use Hanson\Vbot\Message\Text;
use Illuminate\Support\Collection;

class MissionMessageHandler extends AbstractMessageHandler
{

    public $name = 'watcher_mission';

    public $zhName = '';

    public $version = '1.0.0';

    /**
     * @var MessageAnswerInterface[]
     */
    protected $messageAnswers = [];

    /**
     * 注册拓展时的操作.
     */
    public function register()
    {
        $this->messageAnswers = [
            new QueryApiGroup(),
            new ExecuteTesting(),
            new QueryMission(),
        ];
    }

    /**
     *
     * @param Collection $message
     *
     * @return mixed
     */
    public function handler(Collection $message)
    {
        if ('text' === $message['type'] && 'Group' === $message['fromType'] && $message['isAt']) {
            $reply = '开黑吗';
            if ('' === $message['pure']) {
                $reply = '有话说, 有屁放';
            } elseif ($answer = $this->getMatchAnswer($message['pure'])) {
                $reply = $answer->reply($message['pure']);
            } else {
                $response = vbot()['http']->json(
                    'http://www.tuling123.com/openapi/api',
                    [
                        'key' => vbot()['config']['tuling.api_key'],
                        'userid' => vbot()['config']['tuling.user_id'],
                        'info' => $message['pure'],
                    ],
                    true
                );
                $reply = $response['text'] ?? '我什么都不知道';
            }
            Text::send($message['from']['UserName'], $reply);
        }
    }

    protected function getMatchAnswer($message)
    {
        foreach ($this->messageAnswers as $answer) {
            if ($answer->match($message)) {
                return $answer;
            }
        }
        return null;
    }
}
