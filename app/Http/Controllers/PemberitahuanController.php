<?php

namespace App\Http\Controllers;

use Log;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Notifications\MessageSentNotification;

class PemberitahuanController extends Controller
{

    protected $role;
    protected $userId;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->role = Session::get('user.role');
            $this->userId = Session::get('user.id');
            
            // Fallback to Auth guards if session is empty or missing
            if (!$this->role || !$this->userId) {
                foreach (['admin', 'dosen', 'mahasiswa', 'kaprodi', 'direktur', 'wakil_direktur'] as $guard) {
                    if (auth()->guard($guard)->check()) {
                        $user = auth()->guard($guard)->user();
                        $this->role = $guard;
                        $this->userId = $user->id;
                        break;
                    }
                }
            }
            
            return $next($request);
        });
    }
    public function sendMessage(Request $request)
    {
        if ($this->role != 'direktur' && $this->role != 'wakil_direktur' && $this->role != 'kaprodi') {
            $request->validate([
                'message' => 'required|string',
                'jadwal_id' => 'required|exists:jadwals,id',
                'sender_id' => 'required|integer',
                'sender_type' => 'required|string',
                'receiver_type' => 'required|string',
                'receiver_id' => 'required|integer',
            ]);
        } else {
            $request->validate([
                'message' => 'required|string',
                'jadwal_id' => 'required|exists:jadwals,id',
                'sender_id' => 'required|integer',
                'sender_type' => 'required|string',
            ]);
        }

        $jadwal = Jadwal::findOrFail($request->jadwal_id);
        $senderModel = "App\\Models\\" . $request->sender_type;
        $receiverType = $request->receiver_type;

        $receiverType = trim($receiverType, '\\');
        if (strpos($receiverType, 'App\\Models\\') !== 0) {
            $receiverType = ltrim($receiverType, 'App\Models\\');
            $receiverType = "App\\Models\\" . $receiverType;
        }

        if (!class_exists($senderModel)) {
            return response()->json([
                'message' => 'Model pengirim tidak ditemukan.',
            ], 400);
        }

        $sender = $senderModel::find($request->sender_id);
        if (!$sender) {
            return response()->json([
                'message' => 'Data pengirim tidak valid.',
            ], 400);
        }

        if ($this->role == 'direktur' || $this->role == 'wakil_direktur' || $this->role == 'kaprodi') {
            $receiver = Dosen::find($jadwal->dosens_id);

            if (!$receiver) {
                return response()->json([
                    'message' => 'Dosen yang dituju tidak ditemukan.',
                ], 400);
            }

            $lastMessage = Message::where('sender_type', 'App\Models\Dosen')
                ->where('receiver_type', $senderModel)
                ->where('sender_id', $receiver->id)
                ->where('receiver_id', $request->sender_id)
                ->where('jadwal_id', $request->jadwal_id)
                ->orderBy('sent_at', 'desc')
                ->first();

            $parentId = $lastMessage ? $lastMessage->id : null;
            

            $message = Message::create([
                'sender_id' => $request->sender_id,
                'sender_type' => $senderModel,
                'receiver_type' => 'App\Models\Dosen',
                'receiver_id' => $receiver->id,
                'matkul_id' => $jadwal->matkuls_id,
                'message' => $request->message,
                'sent_at' => now(),
                'jadwal_id' => $request->jadwal_id,
                'kelas_id' => $jadwal->kelas_id,
                'parent_id' => $parentId
            ]);

            $message->load('sender');
            $receiver->notify(new MessageSentNotification($message));

            broadcast(new \App\Events\MessageSent($message))->toOthers();
        } elseif ($this->role == 'dosen') {
            $receiver = $receiverType::find($request->receiver_id);

            if (!$receiver) {
                return response()->json([
                    'message' => 'Penerima tidak ditemukan.',
                ], 400);
            }

            $lastMessage = Message::where('sender_type', $receiverType)
                ->where('receiver_type', $senderModel)
                ->where('sender_id', $request->receiver_id)
                ->where('receiver_id', $request->sender_id)
                ->where('jadwal_id', $request->jadwal_id)
                ->orderBy('sent_at', 'desc')
                ->first();

            $parentId = $lastMessage ? $lastMessage->id : null;

            $message = Message::create([
                'sender_id' => $request->sender_id,
                'sender_type' => $senderModel,
                'receiver_type' => $receiverType,
                'receiver_id' => $request->receiver_id,
                'matkul_id' => $jadwal->matkuls_id,
                'message' => $request->message,
                'sent_at' => now(),
                'jadwal_id' => $request->jadwal_id,
                'kelas_id' => $jadwal->kelas_id,
                'parent_id' => $parentId

            ]);

            $message->load('sender');
            $receiver->notify(new MessageSentNotification($message));

            broadcast(new \App\Events\MessageSent($message))->toOthers();
        }

        return response()->json([
            'message' => 'Pesan berhasil dikirim!',
            'data' => $message,
        ]);
    }



    public function getMessages(Request $request)
    {
        $jadwal = Jadwal::findOrFail($request->jadwal_id);

        $receiverType = $this->getModelNamespaceFromRole($this->role);

        $dosenType = $this->getModelNamespaceFromRole('dosen');

        $messages = Message::where('jadwal_id', $jadwal->id)
            ->where(function ($query) use ($jadwal, $receiverType, $dosenType) {
                $query->where(function ($subQuery) use ($jadwal, $receiverType, $dosenType) {
                    $subQuery->where('sender_id', $this->userId)
                        ->where('sender_type', $receiverType)
                        ->where('receiver_id', $jadwal->dosens_id)
                        ->where('receiver_type', $dosenType);
                })->orWhere(function ($subQuery) use ($jadwal, $receiverType, $dosenType) {
                    $subQuery->where('receiver_id', $this->userId)
                        ->where('receiver_type', $receiverType)
                        ->where('sender_id', $jadwal->dosens_id)
                        ->where('sender_type', $dosenType);
                });
            })
            ->orderBy('sent_at', 'asc')
            ->get();

        $this->markMessagesAsRead($messages);

        return response()->json($messages);
    }

    public function getMessagesDosen(Request $request)
    {
        $jadwalId = $request->input('jadwal_id');
        $senderId = $request->input('sender_id');
        $senderType = $request->input('sender_type');

        $normalizedSenderType = $this->normalizeSenderType($senderType);

        $receiverType = $this->getModelNamespaceFromRole($this->role);

        $messages = Message::where('jadwal_id', $jadwalId)
            ->where(function ($query) use ($senderId, $normalizedSenderType, $receiverType) {
                $query->where(function ($subQuery) use ($senderId, $normalizedSenderType, $receiverType) {
                    $subQuery->where('sender_id', $this->userId)
                        ->where('sender_type', $receiverType)
                        ->where('receiver_id', $senderId)
                        ->where('receiver_type', $normalizedSenderType);
                })->orWhere(function ($subQuery) use ($senderId, $normalizedSenderType, $receiverType) {
                    $subQuery->where('receiver_id', $this->userId)
                        ->where('receiver_type', $receiverType)
                        ->where('sender_id', $senderId)
                        ->where('sender_type', $normalizedSenderType);
                });
            })
            ->orderBy('sent_at', 'asc')
            ->get();

        $this->markMessagesAsRead($messages);

        return response()->json($messages);
    }

    public function getJadwalContactsDosen(Request $request)
    {
        $request->validate([
            'jadwal_id' => 'required|exists:jadwals,id'
        ]);

        $jadwal = Jadwal::with('kelas', 'matkul')->findOrFail($request->jadwal_id);

        $messages = Message::where('jadwal_id', $request->jadwal_id)
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('sender_id', $this->userId)
                      ->where('sender_type', 'App\Models\Dosen');
                })->orWhere(function ($q) {
                    $q->where('receiver_id', $this->userId)
                      ->where('receiver_type', 'App\Models\Dosen');
                });
            })
            ->get();

        $contacts = [];

        // 1. Kaprodi of this prodi
        $prodiId = $jadwal->matkul->prodi_id ?? ($jadwal->kelas->id_prodi ?? null);
        if ($prodiId) {
            $kaprodis = \App\Models\Kaprodi::where('status', 1)
                ->whereHas('prodis', function ($q) use ($prodiId) {
                    $q->where('prodi_id', $prodiId);
                })->get();

            foreach ($kaprodis as $kaprodi) {
                $contacts["App\\Models\\Kaprodi_" . $kaprodi->id] = [
                    'id' => $kaprodi->id,
                    'type' => 'App\Models\Kaprodi',
                    'nama' => $kaprodi->nama,
                    'role_label' => 'Kaprodi',
                    'has_messages' => false
                ];
            }
        }

        // 2. Active Wadirs
        $wadirs = \App\Models\Wadir::where('status', 1)->get();
        foreach ($wadirs as $wadir) {
            $contacts["App\\Models\\Wadir_" . $wadir->id] = [
                'id' => $wadir->id,
                'type' => 'App\Models\Wadir',
                'nama' => $wadir->nama,
                'role_label' => 'Wakil Direktur',
                'has_messages' => false
            ];
        }

        // 3. Active Direkturs
        $direkturs = \App\Models\Direktur::where('status', 1)->get();
        foreach ($direkturs as $direktur) {
            $contacts["App\\Models\\Direktur_" . $direktur->id] = [
                'id' => $direktur->id,
                'type' => 'App\Models\Direktur',
                'nama' => $direktur->nama,
                'role_label' => 'Direktur',
                'has_messages' => false
            ];
        }

        // 4. Overwrite messages status for any who have previous chat history
        foreach ($messages as $message) {
            $isSenderDosen = ($message->sender_type === 'App\Models\Dosen');
            $contactId = $isSenderDosen ? $message->receiver_id : $message->sender_id;
            $contactType = $isSenderDosen ? $message->receiver_type : $message->sender_type;

            $roleLabel = 'Pimpinan';
            if ($contactType === 'App\Models\Kaprodi') $roleLabel = 'Kaprodi';
            elseif ($contactType === 'App\Models\Wadir') $roleLabel = 'Wakil Direktur';
            elseif ($contactType === 'App\Models\Direktur') $roleLabel = 'Direktur';

            $key = $contactType . "_" . $contactId;

            if (!isset($contacts[$key])) {
                $contactModel = $contactType;
                $contactUser = $contactModel::find($contactId);
                if ($contactUser) {
                    $contacts[$key] = [
                        'id' => $contactId,
                        'type' => $contactType,
                        'nama' => $contactUser->nama,
                        'role_label' => $roleLabel,
                        'has_messages' => true
                    ];
                }
            } else {
                $contacts[$key]['has_messages'] = true;
            }
        }

        return response()->json(array_values($contacts));
    }



    protected function markMessagesAsRead($messages)
    {
        $modelNamespace = $this->getModelNamespaceFromRole($this->role);

        $unreadMessages = $messages->where('read', false)
            ->where('receiver_id', $this->userId)
            ->where('receiver_type', $modelNamespace);

        foreach ($unreadMessages as $message) {
            $message->update([
                'read' => true,
                'read_at' => now()
            ]);
        }
    }

    public function getUnreadMessageCount(Request $request)
    {
        $modelNamespace = $this->getModelNamespaceFromRole($this->role);
        if ($this->role == 'direktur' || $this->role == 'wakil_direktur' || $this->role == 'kaprodi') {
            $unreadCount = Message::where('receiver_id', $this->userId)
                ->where('sender_id', $request->dosen_id)
                ->where('receiver_type', $modelNamespace)
                ->where('read', false)
                ->count();
        } elseif ($this->role == 'dosen') {
            $unreadCount = Message::where('receiver_id', $this->userId)
                ->where('receiver_type', $modelNamespace)
                ->where('read', false)
                ->count();
        }
        $unreadGet = Message::with('sender', 'kelas', 'jadwal.matkul')->where('receiver_id', $this->userId)
            ->where('receiver_type', $modelNamespace)
            ->where('read', false)
            ->get();

        return response()->json([
            'unread_count' => $unreadCount,
            'unread_get' => $unreadGet
        ]);
    }

    protected function getModelNamespaceFromRole($role)
    {
        $roleToModelMap = [
            'admin' => 'App\Models\Admin',
            'direktur' => 'App\Models\Direktur',
            'wakil_direktur' => 'App\Models\Wadir',
            'kaprodi' => 'App\Models\Kaprodi',
            'mahasiswa' => 'App\Models\Mahasiswa',
            'dosen' => 'App\Models\Dosen'
        ];

        return $roleToModelMap[$role] ?? '';
    }

    protected function normalizeSenderType($senderType)
    {
        if (strpos($senderType, 'AppModels') === 0) {
            return 'App\\Models\\' . str_replace('AppModels', '', $senderType);
        }
        return $senderType;
    }

    public function getUnreadMessageCountByContact($contactId, $contactType)
    {
        if (!str_contains($contactType, 'App\Models\\')) {
            $contactType = 'App\Models\\' . $contactType;
        }

        $modelNamespace = $this->getModelNamespaceFromRole($this->role);

        $unreadCount = Message::where('sender_id', $contactId)
            ->where('sender_type', $contactType)
            ->where('receiver_id', $this->userId)
            ->where('receiver_type', $modelNamespace)
            ->where('read', false)
            ->count();

        return response()->json([
            'unread_count' => $unreadCount
        ]);
    }
}
