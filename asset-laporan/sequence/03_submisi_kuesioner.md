# Sequence Diagram: Submisi Respons Kuesioner Dosen

```mermaid
sequenceDiagram
    autonumber
    actor Mahasiswa
    participant View as Questionnaire/Detail.vue
    participant Ctrl as QuestionnaireController
    participant RespModel as QuestionnaireResponse (Model)
    participant AnsModel as QuestionnaireAnswer (Model)
    participant DB as Database

    Mahasiswa->>View: Input Jawaban Kuesioner (likert scale & teks)
    Mahasiswa->>View: Submit Form Kuesioner
    View->>Ctrl: POST /v2/questionnaire/submit (questionnaire_id, dosen_id, answers)
    Ctrl->>Ctrl: Execute Validasi: IsRequired
    alt Jawaban Belum Lengkap
        Ctrl-->>View: Tampilkan Exception: Form Incomplete
        View-->>Mahasiswa: Render Validation Alert
    else Jawaban Lengkap
        Ctrl->>DB: DB::beginTransaction()
        Ctrl->>RespModel: create(questionnaire_id, respondent_id, respondent_type, dosen_id)
        RespModel->>DB: Insert Response Record
        DB-->>RespModel: Insert Response Success (response_id)
        loop Setiap Jawaban Pertanyaan
            Ctrl->>AnsModel: create(response_id, question_id, answer_value)
            AnsModel->>DB: Insert Answer Record
            DB-->>AnsModel: Insert Answer Success
        end
        Ctrl->>DB: DB::commit()
        Ctrl-->>View: Response Success Event
        View-->>Mahasiswa: Render Success Toast
    end
```

Sequence diagram ini menggambarkan alur umum pengisian kuesioner evaluasi oleh Mahasiswa, yang berlaku untuk evaluasi pelayanan kampus maupun evaluasi kinerja dosen. Mahasiswa mengisi instrumen penilaian serta mengirimkan formulir kuesioner, sistem melakukan validasi kelengkapan jawaban, lalu mengembalikan pesan kesalahan jika ada jawaban yang belum diisi. Setelah data kuesioner valid dan lengkap, sistem menyimpan data respons beserta rincian jawaban ke database dalam satu transaksi aman, dan akhirnya menampilkan notifikasi sukses. Alur ini mewakili mekanisme pengumpulan umpan balik mahasiswa untuk penjaminan mutu internal.
