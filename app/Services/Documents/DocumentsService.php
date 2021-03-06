<?php

declare(strict_types=1);

namespace App\Services\Documents;

use App\Eloquents\Document;
use App\Eloquents\User;
use App\Eloquents\Schedule;
use Illuminate\Http\UploadedFile;

class DocumentsService
{
    /**
     * 配布資料を作成する
     *
     * @param string $name
     * @param string|null $description
     * @param UploadedFile $file
     * @param User $created_by
     * @param boolean $is_public 公開するかどうか
     * @param boolean $is_important 重要かどうか
     * @param Schedule|null $schedule 配布資料に紐付けるスケジュールのID
     * @param string|null $notes スタッフ用メモ
     * @return Document
     */
    public function createDocument(
        string $name,
        ?string $description,
        UploadedFile $file,
        User $created_by,
        bool $is_public,
        bool $is_important,
        ?Schedule $schedule,
        ?string $notes
    ): Document {
        $path = $file->store('documents');

        return Document::create([
            'name' => $name,
            'description' => $description,
            'path' => $path,
            'size' => $file->getSize(),
            'extension' => $file->getClientOriginalExtension(),
            'created_by' => $created_by->id,
            'updated_by' => $created_by->id,
            'is_public' => $is_public,
            'is_important' => $is_important,
            'schedule_id' => !empty($schedule) ? $schedule->id : null,
            'notes' => $notes,
        ]);
    }

    /**
     * 配布資料を更新する
     *
     *
     * @param Document $document 更新対象の配布資料
     * @param string $name
     * @param string|null $description
     * @param UploadedFile|null $file
     * @param User $updated_by
     * @param boolean $is_public 公開するかどうか
     * @param boolean $is_important 重要かどうか
     * @param Schedule|null $schedule 配布資料に紐付けるスケジュールのID
     * @param string|null $notes スタッフ用メモ
     * @return bool
     */
    public function updateDocument(
        Document $document,
        string $name,
        ?string $description,
        ?UploadedFile $file,
        User $updated_by,
        bool $is_public,
        bool $is_important,
        ?Schedule $schedule,
        ?string $notes
    ): bool {
        return $document->update([
            'name' => $name,
            'description' => $description,
            'path' => empty($file) ? $document->path : $file->store('documents'),
            'size' => empty($file) ? $document->size : $file->getSize(),
            'extension' => empty($file) ? $document->extension : $file->getClientOriginalExtension(),
            'updated_by' => $updated_by->id,
            'is_public' => $is_public,
            'is_important' => $is_important,
            'schedule_id' => !empty($schedule) ? $schedule->id : null,
            'notes' => $notes,
        ]);
    }
}
