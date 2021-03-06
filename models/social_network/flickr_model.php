<?php

include_once ('../../../../../wp-config.php');
include_once ('../../../../../wp-load.php');
include_once ('../../../../../wp-includes/wp-db.php');

class FlickrModel {

    /**
     * @clas-name	FlickrModel 
     * @description	Listar fotos públicas automaticamente de um dado usuário do flickr
     * @author     	Saymon de Oliveira Souza (alxsay@hotmail.com)
     * @version    	1.0
     */
    const endPoint = 'https://api.flickr.com/services/rest/?';
    const method = 'method=flickr.';
    const apiKey = '&api_key=';
    const perPage = '&per_page=500';
    const format = '&format=json&nojsoncallback=1';

    private $userId;
    private $userName;
    private $apiKeyValue;

    //apenas um nome válido de usuário é necessário para instanciar a classe
    function __construct($uName, array $config) {
        //necessário tratar os espaços em branco
        $this->userName = str_replace(' ', '+', $uName);
        $this->apiKeyValue = $config['socialdb_flickr_api_id'];
        $this->userId = $this->peopleFindByUsername()['user']['nsid'];
        if ($this->userId) {
            return true;
        } else {
            return false;
        }
    }

    /* @name: callFlickrAPI()
     * @description: método privado para fazer qualquer requisição para a API do flickr
     * @arfs: $req é uma string contendo a url de uma requisição REST
     * 
     * @return: a resposta da API do formato de uma array do php
     * */

    private function callFlickrAPI($req) {
        $response = file_get_contents($req);
        $jsonResponse = json_decode($response, true);
        return $jsonResponse;
    }

    /* @name: peopleFindByUsername()
     * @description: método público para recuperar a nsid do usuário
     * @return: a nsid de um dado usuário flickr necessário para realizar outras operações
     * como, por exemplo, listar as fotos públicas deste usuário
     * */

    private function peopleFindByUsername() {
        $request = self::endPoint . self::method . 'people.findByUsername' . self::apiKey . $this->apiKeyValue . '&username=' . $this->userName . self::format;
        return $this->callFlickrAPI($request);
    }

    /* @name: peopleGetPublicPhotos()
     * @description: método público para recuperar no máximo 500 fotos do usuário
     * @return: array php contendo os dados de no máximo 500 fotos de um usuário
     * */

    private function peopleGetPublicPhotos($page = 1) {
        $numPage = $page;
        $request = self::endPoint . self::method . 'people.getPublicPhotos' . self::apiKey . $this->apiKeyValue . '&user_id=' . $this->userId . self::perPage . '&page=' . $numPage . self::format;
        $reponse = $this->callFlickrAPI($request);
        $arrayResponse = array(array('pages' => $reponse['photos']['pages'], 'total' => $reponse['photos']['total']));
        foreach ($reponse['photos']['photo'] as &$photo) {
            $arrayResponse[] = array(
                'title' => $photo['title'],
                'url' => 'https://farm' . $photo['farm'] . '.staticflickr.com/' . $photo['server'] . '/' . $photo['id'] . '_' . $photo['secret'] . '_b.jpg',
                //'date' => $photo['dateupload'],
                'embed' => '<embed width="200" height="200" src="' . 'https://farm' . $photo['farm'] . '.staticflickr.com/' . $photo['server'] . '/' . $photo['id'] . '_' . $photo['secret'] . '_b.jpg" frameborder="0" allowfullscreen></iframe>',
                'thumbnail' => 'https://farm' . $photo['farm'] . '.staticflickr.com/' . $photo['server'] . '/' . $photo['id'] . '_' . $photo['secret'] . '_b.jpg'
            );
        }
        return $arrayResponse;
    }

    /* @name: peopleGetAllPublicPhotos()
     * @description: método público para todas as fotos públicas de um usuário
     * @return: array php contendo os dados de no máximo 500 fotos de um usuário
     * 
     */

    public function peopleGetAllPublicPhotos(array $data, ObjectModel $object_model) {
        $firstRequest = $this->peopleGetPublicPhotos();
        $numPages = (int) $firstRequest[0]['pages'];
        $totalPhotos = (int) $firstRequest[0]['total'];
        if (($numPages >= 1) && ($totalPhotos >= 1)) {
            set_time_limit(0);
            // numero de fotos inseridas no banco
            $numRecortedPhotos = 0;
            //insere data de importação
            $dateUpdate = date('d/m/y');
            FlickrModel::setLastDateFlickr($data['identifierId'], $dateUpdate);
            FlickrModel::setImportStatus($data['identifierId'], 1);
            foreach ($firstRequest as &$fPhoto) {
                $post_id = $object_model->add_photo($data['collection_id'], $fPhoto['title'], $fPhoto['embed']);
                if ($post_id) {
                    $object_model->add_thumbnail_url($fPhoto['thumbnail'], $post_id);
                    add_post_meta($post_id, 'socialdb_uri_imported', $fPhoto['thumbnail']);
                    $numRecortedPhotos++;
                }
            }
            unset($firstRequest);
            if ($numPages > 1) {
                $page = 2;
                for ($i = 2; $i <= $numPages; $i++) {
                    $photos = &$this->peopleGetPublicPhotos($page);
                    foreach ($photos as &$photo) {
                        $post_id = $object_model->add_photo($data['collection_id'], $photo['title'], $photo['embed']);
                        if ($post_id) {
                            $object_model->add_thumbnail_url($photo['thumbnail'], $post_id);
                            add_post_meta($post_id, 'socialdb_uri_imported', $photo['thumbnail']);
                            $numRecortedPhotos++;
                        }
                    }
                    unset($photos);
                    $page++;
                }
            }
            return ($numRecortedPhotos > 0) ? true : false;
        } else {
            return false;
        }
    }

    /* @name: getPhotosFromUser()
     * @description: método público para recuperar no máximo 500 fotos do usuário
     * @return: array php contendo os dados de no máximo 500 fotos de um usuário
     * url de requisição - https://api.flickr.com/services/rest/?method=flickr.photos.search&api_key=ac2e2ea27cd77719248aab0439ea2bab&user_id=45950435%40N05&sort=date-posted-asc&content_type=1&extras=views,media,date_upload,license,date_taken&per_page=500&page=1&format=json
     */

    private function getPhotosFromUser($page) {
        $request = self::endPoint . self::method . 'photos.search' . self::apiKey . $this->apiKeyValue . '&user_id=' . $this->userId . '&sort=date-posted-asc&content_type=1&extras=views,date_upload,license,date_taken' . self::perPage . '&page=' . $page . self::format;
        $reponse = $this->callFlickrAPI($request);
        $arrayResponse = array(array('pages' => $reponse['photos']['pages'], 'total' => $reponse['photos']['total']));
        foreach ($reponse['photos']['photo'] as &$photo) {
            $arrayResponse[] = array(
                'title' => $photo['title'],
                'url' => 'https://farm' . $photo['farm'] . '.staticflickr.com/' . $photo['server'] . '/' . $photo['id'] . '_' . $photo['secret'] . '_b.jpg',
                'date' => $photo['dateupload'],
                'datetaken' => $photo['datetaken'],
                'views' => $photo['views'],
                'embed' => 'https://farm' . $photo['farm'] . '.staticflickr.com/' . $photo['server'] . '/' . $photo['id'] . '_' . $photo['secret'] . '_b.jpg',
                'embed_html' => '<embed width="200" height="200" src="' . 'https://farm' . $photo['farm'] . '.staticflickr.com/' . $photo['server'] . '/' . $photo['id'] . '_' . $photo['secret'] . '_b.jpg" frameborder="0" allowfullscreen></iframe>',
                'thumbnail' => 'https://farm' . $photo['farm'] . '.staticflickr.com/' . $photo['server'] . '/' . $photo['id'] . '_' . $photo['secret'] . '_b.jpg'
            );
        }
        return $arrayResponse;
    }

    /* @name: getPhotosFromUser()
     * @description: método público para recuperar no máximo 500 fotos do usuário
     * @return: array php contendo os dados de no máximo 500 fotos de um usuário
     * url de requisição - https://api.flickr.com/services/rest/?method=flickr.photos.search&api_key=ac2e2ea27cd77719248aab0439ea2bab&user_id=45950435%40N05&sort=date-posted-asc&content_type=1&extras=views,media,date_upload,license,date_taken&per_page=500&page=1&format=json
     */

    public function getAllPhotosFromUser(array $data, ObjectModel $object_model) {
        // índice da primeira página de resposta
        $currentPage = 1;
        // photos inclusas no banco
        $numRecortedPhotos = 0;
        // primeira requisição
        $response = $this->getPhotosFromUser($currentPage);
        // número de photos na requisição
        $numPhotos = count($response) - 1;
        // paginação
        $numPages = $response[0]['pages'];

        if (!empty($response)) {
            //altera status da importação
            self::setImportStatus($data['identifierId'], 1);
            // trata a primeira resposta: máximo de 500 photos
            for ($i = 1; $i <= $numPhotos; $i++) {
                $post_id = $object_model->add_photo($data['collection_id'], $response[$i]['title'], $response[$i]['embed']);
                if ($post_id) {
                    self::setLastDateFlickr($data['identifierId'], $response[$i]['date']);
                    $object_model->add_thumbnail_url($response[$i]['thumbnail'], $post_id);
                    add_post_meta($post_id, 'socialdb_uri_imported', $response[$i]['thumbnail']);
                    $numRecortedPhotos++;
                }
            }
            unset($response);
            // verifica se há mais de 500 photos
            if ($numPages > 1) {
                // esse loop só se repetirá caso exista mais de 1000 photos
                for ($i = 1; $i < $numPages; $i++) {
                    $currentPage++;
                    $response = $this->getPhotosFromUser($currentPage);
                    $numPhotos = count($response) - 1;
                    // inserindo as photos no banco
                    for ($i = 1; $i <= $numPhotos; $i++) {
                        $post_id = $object_model->add_photo($data['collection_id'], $response[$i]['title'], $response[$i]['embed']);
                        if ($post_id) {
                            self::setLastDateFlickr($data['identifierId'], $response[$i]['date']);
                            $object_model->add_thumbnail_url($response[$i]['thumbnail'], $post_id);
                            add_post_meta($post_id, 'socialdb_uri_imported', $response[$i]['thumbnail']);
                            $numRecortedPhotos++;
                        };
                    }
                    unset($response);
                }
                return ($numRecortedPhotos > 0) ? true : false;
            }
        }
        return ($numRecortedPhotos > 0) ? true : false;
    }

    /* @name: peopleGetPublicPhotosUpdated()
     * @description: método público para todas as fotos públicas de um usuário
     * @return: array php contendo os dados de no máximo 500 fotos de um usuário
     * 
     */

    private function peopleGetPublicPhotosUpdated($date, $page) {
        $request = self::endPoint . self::method . 'photos.search' . self::apiKey . $this->apiKeyValue . '&user_id=' . $this->userId . '&min_upload_date=' . $date . '&sort=date-posted-asc&content_type=1&extras=views,date_upload,license,date_taken' . self::perPage . '&page=' . $page . self::format;
        $reponse = $this->callFlickrAPI($request);
        $arrayResponse = array(array('pages' => $reponse['photos']['pages'], 'total' => $reponse['photos']['total']));
        foreach ($reponse['photos']['photo'] as &$photo) {
            $arrayResponse[] = array(
                'title' => $photo['title'],
                'url' => 'https://farm' . $photo['farm'] . '.staticflickr.com/' . $photo['server'] . '/' . $photo['id'] . '_' . $photo['secret'] . '_b.jpg',
                'date' => $photo['dateupload'],
                'datetaken' => $photo['datetaken'],
                'views' => $photo['views'],
                'embed_html' => '<embed width="200" height="200" src="' . 'https://farm' . $photo['farm'] . '.staticflickr.com/' . $photo['server'] . '/' . $photo['id'] . '_' . $photo['secret'] . '_b.jpg" frameborder="0" allowfullscreen></iframe>',
                'embed' => 'https://farm' . $photo['farm'] . '.staticflickr.com/' . $photo['server'] . '/' . $photo['id'] . '_' . $photo['secret'] . '_b.jpg',
                'thumbnail' => 'https://farm' . $photo['farm'] . '.staticflickr.com/' . $photo['server'] . '/' . $photo['id'] . '_' . $photo['secret'] . '_b.jpg'
            );
        }
        return $arrayResponse;
    }

    /* @name: peopleGetAllPublicPhotosUpdated()
     * @description: método público para todas as fotos públicas de um usuário
     * @return: array php contendo os dados de no máximo 500 fotos de um usuário
     * 
     */

    public function peopleGetAllPublicPhotosUpdated(array $data, ObjectModel $object_model) {
        // data da última foto importada
        $dataLastPhotoImported = trim($data['data']);
        $curr_time = date("Y-m-d H:i:s", $dataLastPhotoImported + 60);
        $dataLastPhotoImported = strtotime($curr_time);

        // índice da primeira página de resposta
        $currentPage = 1;
        // photos inclusas no banco
        $numRecortedPhotos = 0;
        // primeira requisição
        $response = $this->peopleGetPublicPhotosUpdated($dataLastPhotoImported, $currentPage);

        // número de photos na requisição
        $numPhotos = count($response) - 1;
        // paginação
        $numPages = $response[0]['pages'];

        if (!empty($response)) {
            if ($numPhotos > 0) {
                //altera status da importação
                self::setImportStatus($data['identifierId'], 1);
                // trata a primeira resposta: máximo de 500 photos
                for ($i = 1; $i <= $numPhotos; $i++) {
                    $post_id = $object_model->add_photo($data['collection_id'], $response[$i]['title'], $response[$i]['embed']);
                    if ($post_id) {
                        self::setLastDateFlickr($data['identifierId'], $response[$i]['date']);
                        $object_model->add_thumbnail_url($response[$i]['thumbnail'], $post_id);
                        add_post_meta($post_id, 'socialdb_uri_imported', $response[$i]['thumbnail']);
                        $numRecortedPhotos++;
                    }
                }
                unset($response);
                // verifica se há mais de 500 photos
                if ($numPages > 1) {
                    // esse loop só se repetirá caso exista mais de 1000 photos
                    for ($i = 1; $i < $numPages; $i++) {
                        $currentPage++;
                        $response = $this->peopleGetPublicPhotosUpdated($dataLastPhotoImported, $currentPage);
                        $numPhotos = count($response) - 1;
                        // inserindo as photos no banco
                        for ($i = 1; $i <= $numPhotos; $i++) {
                            $post_id = $object_model->add_photo($data['collection_id'], $response[$i]['title'], $response[$i]['embed']);
                            if ($post_id) {
                                self::setLastDateFlickr($data['identifierId'], $response[$i]['date']);
                                $object_model->add_thumbnail_url($response[$i]['thumbnail'], $post_id);
                                add_post_meta($post_id, 'socialdb_uri_imported', $response[$i]['thumbnail']);
                                $numRecortedPhotos++;
                            };
                        }
                        unset($response);
                    }
                    return ($numRecortedPhotos > 0) ? true : false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
        return ($numRecortedPhotos > 0) ? true : false;
    }

    /**
     * @description - function insert_flickr_identifier($identifier)
     * $identifier é o nome do usuário do perfil flickr 
     * Insere um identificador de canal no banco
     * 
     * @autor: Saymon 
     */
    public static function insert_flickr_identifier($identifier, $colectionId) {
        $postId = wp_insert_post(['post_title' => $identifier, 'post_status' => 'publish', 'post_type' => 'socialdb_channel']);
        if ($postId) {
            add_post_meta($postId, 'socialdb_flickr_identificator', $colectionId);
            add_post_meta($postId, 'socialdb_flickr_identificator_last_update', '');
            add_post_meta($postId, 'socialdb_flickr_import_status', 0);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @description - function edit_flickr_identifier($identifier)
     * $identifier -  o nome do usuário do perfil flickr 
     * $newIdentifier - novo valor  
     * altera um identificador de um dado perfil flickr
     * 
     * @autor: Saymon 
     */
    public static function edit_flickr_identifier($identifier, $newIdentifier) {
        if (!empty($newIdentifier)) {
            $my_post = array(
                'ID' => $identifier,
                'post_title' => $newIdentifier,
            );
            $postEdted = wp_update_post($my_post);
            return ($postEdted) ? true : false;
        } else {
            return false;
        }
    }

    /**
     * @description - function delete_flickr_identifier($identifier)
     * $identifier -  o nome do usuário do perfil flickr 
     * $colectionId - coleção a que o identificador pertence  
     * exclui um identificador de um dado perfil flickr
     * 
     * @autor: Saymon 
     */
    public static function delete_flickr_identifier($identifierId, $colectionId) {
        $deletedIdentifier = wp_delete_post($identifierId);
        if ($deletedIdentifier) {
            delete_post_meta($identifierId, 'socialdb_flickr_identificator', $identifier);
            delete_post_meta($identifierId, 'socialdb_flickr_identificator', $colectionId);
            return true;
        } else {

            return false;
        }
    }

    public static function list_flickr_identifier($collectionId) {
        //array de configuração dos parâmetros de get_posts()
        $args = array(
            'meta_key' => 'socialdb_flickr_identificator',
            'meta_value' => $collectionId,
            'post_type' => 'socialdb_channel',
            'post_status' => 'publish',
            'suppress_filters' => true
        );
        $results = get_posts($args);
        if (is_array($results)) {
            $json = [];
            foreach ($results as $ch) {
                if (!empty($ch)) {
                    $postMetaLastUpdate = get_post_meta($ch->ID, 'socialdb_flickr_identificator_last_update');
                    $postMetaImportSatus = get_post_meta($ch->ID, 'socialdb_flickr_import_status');
                    $array = array('name' => $ch->post_title, 'id' => $ch->ID, 'lastUpdate' => $postMetaLastUpdate, 'importStatus' => $postMetaImportSatus);
                    $json['identifier'][] = $array;
                }
            }
            echo json_encode($json);
        } else {
            return false;
        }
    }

    static function setLastDateFlickr($post_id, $date) {
        update_post_meta($post_id, 'socialdb_flickr_identificator_last_update', $date);
    }

    static function setImportStatus($post_id, $date) {
        update_post_meta($post_id, 'socialdb_flickr_import_status', $date);
    }

}

?>