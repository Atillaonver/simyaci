<?php
// File: admin/controller/common/chunk_form.php
namespace Opencart\Admin\Controller\Common;
class ChunkForm extends \Opencart\System\Engine\Controller {

    public function index(): void {

        // Parça numarası
        $chunk_index = isset($this->request->post['chunk_index']) ? (int)$this->request->post['chunk_index'] : 0;

        // Toplam parça sayısı
        $chunk_total = isset($this->request->post['chunk_total']) ? (int)$this->request->post['chunk_total'] : 1;

        // JSON parçası
        $chunk_data = isset($this->request->post['chunk_data']) ? $this->request->post['chunk_data'] : '';

        // Admin session ID → benzersiz tmp dosyası
        $session_id = $this->session->getId();
        $temp_file = DIR_STORAGE . "upload/admin_chunk_" . $session_id . ".tmp";

        // Bu chunk’ı geçici dosyaya ekle
        file_put_contents($temp_file, $chunk_data, FILE_APPEND);

        $json = [];

        // Parçalar bitmemişse → devam et
        if ($chunk_index + 1 < $chunk_total) {

            $json['status'] = 'continue';
            $json['received_chunk'] = $chunk_index;

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        // Son parça geldi → JSON oluştur
        $json_str = file_get_contents($temp_file);
        $full_form_data = json_decode($json_str, true);

        if (!$full_form_data) {
            $json['status'] = 'error';
            $json['message'] = 'Form JSON çözülemedi';

            $this->response->setOutput(json_encode($json));
            return;
        }

        /*
         * NULL → "" dönüştürme
         * (admin tarafındaki oc_strlen hatasını çözer)
         */
        array_walk_recursive($full_form_data, function (&$value) {
            if ($value === null) $value = '';
        });

        // Artık tüm form birleşti → burada istediğin işlemi yapabilirsin
        // Örneğin veritabanına kaydedebilirsin

        // Temizlik
        unlink($temp_file);

        $json['status'] = 'done';
        $json['message'] = 'Form başarıyla birleştirildi';
        $json['full_data'] = $full_form_data;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
?>
