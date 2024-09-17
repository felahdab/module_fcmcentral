<?php
namespace Modules\FcmCentral\Services;

use Modules\FcmUnite\Events\RemoteGeneratedEvent;
use Modules\FcmUnite\Events\UserGeneratedEvent;
use Modules\FcmUnite\Models\ParcoursSerialise;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use Modules\FcmCommun\Http\Integrations\Connectors\CentralFcmServerConnector;

use Modules\FcmCommun\Http\Integrations\Requests\RetreiveUserHistoryRequest;
use Modules\FcmCommun\Http\Integrations\Requests\PushUsergeneratedEventToFcmCentral;

class RemoteApiClientService
{

    public static function postEvent(UserGeneratedEvent $event)
    {
        return true;
    }

    public static function newUser(UserGeneratedEvent $event)
    {
        $nid = $event->detail["nid"];
        $email = $event->detail["email"];
        return true;
    }

    public static function retreiveUserUuid(UserGeneratedEvent $event)
    {
        return true;
    }

    public static function retreiveUserHistory()
    {
        $connector = new CentralFcmServerConnector();
        $request = new RetreiveUserHistoryRequest('048be6a7-6960-4f00-847e-5e9a74dd0238');

        $response = $connector->send($request);

        // dump($response->json());
        // return;

        //$url = "https://c2n.adalfantln.marine.defensecdd.gouv.fr/ffast/api/fcmcentral/v1/get_user_history/048be6a7-6960-4f00-847e-5e9a74dd0238";
        // $url = "http://web/ffast/api/fcmcentral/v1/get_user_history/048be6a7-6960-4f00-847e-5e9a74dd0238";
        // $token = "2|Sa0ancKa0oKjQejKe0UPlKswMcKlHPHMNNOGdKoI9fe2aee9";
        // $response = Http::withOptions([
        //                     'verify' => false,
        //                     ])
        //                 ->withToken($token)
        //                 ->acceptJson()
        //                 ->timeout(1)
        //                 ->get($url);

        //dump($response);
        if ($response->successful()){
            foreach($response->json() as $eventarr){
                $eventarr["detail"] = array(json_decode($eventarr["detail"]));
                //dump($eventarr);

                $newEvent = RemoteGeneratedEvent::from($eventarr);
                event($newEvent);
                dump($newEvent);
            }
        }

        return $response;
    }

    public static function updateParcoursSerialises()
    {
        //$url = "https://c2n.adalfantln.marine.defensecdd.gouv.fr/ffast/api/fcmcentral/v1/get_user_history/048be6a7-6960-4f00-847e-5e9a74dd0238";
        $url = "http://web/ffast/api/fcmcentral/v1/tous_parcours_serialises";
        $token = "2|Sa0ancKa0oKjQejKe0UPlKswMcKlHPHMNNOGdKoI9fe2aee9";
        $response = Http::withOptions([
                            'verify' => false,
                            ])
                        ->withToken($token)
                        ->acceptJson()
                        ->timeout(1)
                        ->get($url);

        //dump($response);
        if ($response->successful()){
            foreach($response->json() as $parcoursserialise){
                // $p = ParcoursSerialise::find($parcoursserialise["id"]); 
                // if ($p == null){
                //     dump('n existe pas deja: ' . $parcoursserialise["id"]);
                //     $p = new ParcoursSerialise();
                //     $p->fill($parcoursserialise);
                //     $p->save();
                // }
                // else 
                // {
                //     dump('deja existant');
                // }
                ParcoursSerialise::updateOrCreate(
                    [ "id" => $parcoursserialise["id"] ],
                    $parcoursserialise
                );
                
                //dump($parcoursserialise);
            }
        }

        return $response;
    }

    public static function push_user_generated_event_to_remote_fcmcentral_instance($url, $token, $event)
    {
        $connector = new CentralFcmServerConnector($url, $token);
        $request = new PushUsergeneratedEventToFcmCentral($event);

        $response = $connector->send($request);

        return $response->throw();

        Log::info('Request sent');
        Log::info($response->json());
    }

}