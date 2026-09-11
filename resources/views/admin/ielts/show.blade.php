@extends('admin.layouts.main')

@section('content')
<div class="row">
  <div class="col-lg-12">
    <!-- Test Overview Header -->
    <div class="card-style mb-30">
      <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
        <div>
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
            <a href="{{ route('admin.ielts.index') }}" class="text-sm text-primary" style="text-decoration:none;">
              <i class="mdi mdi-arrow-left"></i> IELTS testlariga qaytish
            </a>
            <span>•</span>
            @if($test->type === 'placement')
              <span class="badge bg-primary">Placement</span>
            @else
              <span class="badge bg-purple" style="background:#7c3aed;color:#fff;">Full Mock</span>
            @endif
            @if($test->is_active)
              <span class="badge bg-success">Faol</span>
            @else
              <span class="badge bg-secondary">Nofaol</span>
            @endif
          </div>
          <h4 class="mb-5">{{ $test->title }}</h4>
          <p class="text-sm text-gray">
            <i class="mdi mdi-clock-outline me-1"></i> {{ $test->time_limit_minutes }} daqiqa
            <span class="mx-2">•</span>
            <i class="mdi mdi-layers-outline me-1"></i> {{ $test->sections->count() }} ta bo'lim
            <span class="mx-2">•</span>
            <i class="mdi mdi-help-circle-outline me-1"></i> {{ $test->sections->flatMap->passages->flatMap->questions->count() }} ta umumiy savol
          </p>
        </div>
        <div style="display:flex;gap:8px;">
          <a href="{{ route('admin.ielts.results', $test) }}" class="main-btn light-btn btn-hover btn-sm">
            <i class="mdi mdi-chart-box-outline me-1"></i> Natijalar
          </a>
          <a href="{{ route('admin.ielts.edit', $test) }}" class="main-btn warning-btn btn-hover btn-sm">
            <i class="mdi mdi-pencil me-1"></i> Tahrirlash
          </a>
        </div>
      </div>
    </div>

    <!-- Skill Sections Navigation Tabs -->
    <div class="card-style mb-30">
      <ul class="nav nav-pills mb-25" id="ieltsSkillTabs" role="tablist" style="gap: 8px;">
        @foreach($test->sections as $index => $section)
          @php
            $qCount = $section->passages->flatMap->questions->count();
            $icon = match($section->skill) {
              'reading' => 'mdi-book-open-variant',
              'listening' => 'mdi-headphones',
              'writing' => 'mdi-pencil-box-outline',
              'speaking' => 'mdi-microphone',
              default => 'mdi-format-list-bulleted'
            };
          @endphp
          <li class="nav-item" role="presentation">
            <button class="nav-link {{ $index === 0 ? 'active' : '' }}" id="tab-{{ $section->id }}" data-bs-toggle="pill" data-bs-target="#content-{{ $section->id }}" type="button" role="tab" style="border-radius: 8px; font-weight: 600; padding: 10px 18px;">
              <i class="mdi {{ $icon }} me-1"></i>
              {{ ucfirst($section->skill) }}
              <span class="badge bg-secondary ms-1" style="font-size: 0.75rem;">{{ $qCount }}</span>
            </button>
          </li>
        @endforeach
      </ul>

      <div class="tab-content" id="ieltsSkillTabsContent">
        @foreach($test->sections as $index => $section)
          <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="content-{{ $section->id }}" role="tabpanel">
            
            <div style="display:flex;justify-content:space-between;align-items:center;background:#f8fafc;padding:14px 18px;border-radius:10px;margin-bottom:20px;border:1px solid #e2e8f0;flex-wrap:wrap;gap:10px;">
              <div>
                <h6 style="margin:0;text-transform:capitalize;">{{ $section->skill }} Bo'limi</h6>
                <small class="text-muted">
                  @if($section->skill === 'reading') Matnlar (passages) va ularga oid test savollarini boshqaring.
                  @elseif($section->skill === 'listening') Audio eshittirishlar va tinglab javob berish savollari.
                  @elseif($section->skill === 'writing') Insho / Writing topshiriqlari (AI avtomatik baholaydi).
                  @else Suhbat / Speaking savollari. @endif
                </small>
              </div>
              <button type="button" class="main-btn primary-btn btn-hover btn-sm" data-bs-toggle="modal" data-bs-target="#addPassageModal-{{ $section->id }}">
                <i class="mdi mdi-plus me-1"></i> 
                @if($section->skill === 'listening') Yangi Audio / Passage
                @elseif($section->skill === 'writing') Yangi Writing Mavzusi
                @else Yangi Matn (Passage) @endif
              </button>
            </div>

            <!-- Passages list in this section -->
            @if($section->passages->isEmpty())
              <div class="text-center py-5 border rounded" style="border-style:dashed!important;border-color:#cbd5e1!important;">
                <i class="mdi mdi-file-document-outline" style="font-size: 40px; color: #94a3b8;"></i>
                <p class="mt-2 text-muted">Ushbu bo'limda hozircha hech qanday matn yoki vazifa qo'shilmagan.</p>
                <button type="button" class="main-btn primary-btn btn-hover btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#addPassageModal-{{ $section->id }}">
                  <i class="mdi mdi-plus me-1"></i> Yangi qo'shish
                </button>
              </div>
            @else
              @foreach($section->passages as $pIndex => $passage)
                <div class="border rounded p-4 mb-25" style="background:#fff;border-color:#e2e8f0!important;box-shadow:0 1px 3px rgba(0,0,0,0.03);">
                  <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:12px;">
                    <div>
                      <span class="badge bg-light text-dark border mb-1">Matn #{{ $pIndex + 1 }}</span>
                      <h5 style="margin:0;font-size:1.1rem;">{{ $passage->title ?: 'Nomsiz matn / vazifa' }}</h5>
                    </div>
                    <div style="display:flex;gap:6px;">
                      <button type="button" class="main-btn warning-btn btn-hover btn-sm" data-bs-toggle="modal" data-bs-target="#editPassageModal-{{ $passage->id }}" title="Tahrirlash">
                        <i class="mdi mdi-pencil"></i>
                      </button>
                      <form method="POST" action="{{ route('admin.ielts.passages.destroy', $passage) }}" onsubmit="return confirm('Ushbu matn va unga tegishli barcha savollar o\'chiriladi. Davom etasizmi?');" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="main-btn danger-btn btn-hover btn-sm" title="O'chirish">
                          <i class="mdi mdi-delete"></i>
                        </button>
                      </form>
                    </div>
                  </div>

                  @if($passage->audio_url)
                    <div class="mb-3 p-2 bg-light rounded border">
                      <div class="text-xs text-muted mb-1"><i class="mdi mdi-volume-high"></i> Audio fayl:</div>
                      <audio controls class="w-100" style="max-height: 40px;">
                        <source src="{{ $passage->audio_url }}">
                      </audio>
                    </div>
                  @endif

                  @if($passage->content)
                    <div class="p-3 bg-light rounded text-sm mb-3" style="max-height: 180px; overflow-y: auto; white-space: pre-line; border: 1px solid #f1f5f9; line-height: 1.6;">{{ $passage->content }}</div>
                  @endif

                  <!-- Questions Under This Passage -->
                  <div class="mt-4 pt-3 border-top">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                      <h6 style="font-size:0.95rem;margin:0;">
                        <i class="mdi mdi-help-circle-outline text-primary me-1"></i>
                        Ushbu matnga oid savollar ({{ $passage->questions->count() }} ta)
                      </h6>
                      <button type="button" class="main-btn primary-btn btn-hover btn-sm" data-bs-toggle="modal" data-bs-target="#addQuestionModal-{{ $passage->id }}">
                        <i class="mdi mdi-plus me-1"></i> Savol qo'shish
                      </button>
                    </div>

                    @if($passage->questions->isEmpty())
                      <p class="text-sm text-muted mb-0 py-2">Ushbu matn uchun hali savollar qo'shilmagan.</p>
                    @else
                      <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0" style="font-size: 0.9rem;">
                          <thead class="table-light">
                            <tr>
                              <th style="width: 50px;">#</th>
                              <th>Savol matni</th>
                              <th>Turi</th>
                              <th>Variantlar</th>
                              <th>To'g'ri javob</th>
                              <th style="width: 100px;">Amal</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach($passage->questions as $qIndex => $question)
                              <tr>
                                <td><strong>{{ $question->order ?? ($qIndex + 1) }}</strong></td>
                                <td style="max-width: 320px;">
                                  <div style="font-weight: 500;">{{ $question->question_text }}</div>
                                </td>
                                <td>
                                  @if($question->type === 'multiple_choice')
                                    <span class="badge bg-info text-dark">Multiple Choice</span>
                                  @elseif($question->type === 'true_false_ng')
                                    <span class="badge bg-warning text-dark">True/False/NG</span>
                                  @elseif($question->type === 'writing_task')
                                    <span class="badge bg-primary">Writing Task</span>
                                  @elseif($question->type === 'speaking_task')
                                    <span class="badge bg-danger">Speaking</span>
                                  @else
                                    <span class="badge bg-secondary">{{ $question->type }}</span>
                                  @endif
                                </td>
                                <td>
                                  @if(!empty($question->options))
                                    <small class="text-muted">{{ implode(' | ', $question->options) }}</small>
                                  @else
                                    <small class="text-muted">—</small>
                                  @endif
                                </td>
                                <td>
                                  @if($question->correct_answer)
                                    <span class="badge bg-success" style="font-size:0.8rem;">{{ $question->correct_answer }}</span>
                                  @else
                                    <small class="text-muted">AI tekshiradi</small>
                                  @endif
                                </td>
                                <td>
                                  <div style="display:flex;gap:4px;">
                                    <button type="button" class="btn btn-sm btn-outline-warning p-1" data-bs-toggle="modal" data-bs-target="#editQuestionModal-{{ $question->id }}" title="Tahrirlash">
                                      <i class="mdi mdi-pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.ielts.questions.destroy', $question) }}" onsubmit="return confirm('Ushbu savolni o\'chirmoqchimisiz?');" style="display:inline;">
                                      @csrf
                                      @method('DELETE')
                                      <button type="submit" class="btn btn-sm btn-outline-danger p-1" title="O'chirish">
                                        <i class="mdi mdi-delete"></i>
                                      </button>
                                    </form>
                                  </div>
                                </td>
                              </tr>

                              <!-- Edit Question Modal -->
                              <div class="modal fade" id="editQuestionModal-{{ $question->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                  <div class="modal-content">
                                    <form method="POST" action="{{ route('admin.ielts.questions.update', $question) }}">
                                      @csrf
                                      @method('PUT')
                                      <div class="modal-header">
                                        <h5 class="modal-title">Savolni tahrirlash</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                      </div>
                                      <div class="modal-body">
                                        <div class="mb-3">
                                          <label class="form-label">Savol turi <span class="text-danger">*</span></label>
                                          <select name="type" class="form-select" required>
                                            <option value="multiple_choice" {{ $question->type === 'multiple_choice' ? 'selected' : '' }}>Multiple Choice (Bir nechta variantli)</option>
                                            <option value="true_false_ng" {{ $question->type === 'true_false_ng' ? 'selected' : '' }}>True / False / Not Given</option>
                                            <option value="writing_task" {{ $question->type === 'writing_task' ? 'selected' : '' }}>Writing Task (Matn yozish topshirig'i)</option>
                                            <option value="speaking_task" {{ $question->type === 'speaking_task' ? 'selected' : '' }}>Speaking Task (Ovozli gapirish topshirig'i)</option>
                                          </select>
                                        </div>
                                        <div class="mb-3">
                                          <label class="form-label">Savol matni <span class="text-danger">*</span></label>
                                          <textarea name="question_text" class="form-control" rows="3" required>{{ $question->question_text }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                          <label class="form-label">Variantlar (faqat Multiple Choice uchun, har bir variant yangi qatorda)</label>
                                          <textarea name="options_raw" class="form-control" rows="4" placeholder="A variant&#10;B variant&#10;C variant&#10;D variant">{{ !empty($question->options) ? implode("\n", $question->options) : '' }}</textarea>
                                          <small class="text-muted">True/False/Not Given tanlansa, variantlar avtomatik shakllanadi.</small>
                                        </div>
                                        <div class="row">
                                          <div class="col-md-8">
                                            <div class="mb-3">
                                              <label class="form-label">To'g'ri javob</label>
                                              <input type="text" name="correct_answer" class="form-control" value="{{ $question->correct_answer }}" placeholder="Masalan: True yoki B variant matni">
                                              <small class="text-muted">Multiple choice bo'lsa, to'g'ri variant matnini aynan yozing.</small>
                                            </div>
                                          </div>
                                          <div class="col-md-4">
                                            <div class="mb-3">
                                              <label class="form-label">Tartib raqami</label>
                                              <input type="number" name="order" class="form-control" value="{{ $question->order }}">
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="modal-footer">
                                        <button type="button" class="main-btn light-btn btn-sm" data-bs-dismiss="modal">Bekor qilish</button>
                                        <button type="submit" class="main-btn primary-btn btn-sm">Saqlash</button>
                                      </div>
                                    </form>
                                  </div>
                                </div>
                              </div>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    @endif
                  </div>
                </div>

                <!-- Edit Passage Modal -->
                <div class="modal fade" id="editPassageModal-{{ $passage->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                      <form method="POST" action="{{ route('admin.ielts.passages.update', $passage) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                          <h5 class="modal-title">Matn / Vazifani tahrirlash</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <div class="mb-3">
                            <label class="form-label">Sarlavha (ixtiyoriy)</label>
                            <input type="text" name="title" class="form-control" value="{{ $passage->title }}" placeholder="Masalan: The Silk Road Legacy in Samarkand">
                          </div>

                          @if($section->skill === 'listening')
                            <div class="mb-3">
                              <label class="form-label">Yangi Audio fayl yuklash (mp3, wav, m4a - max 30MB)</label>
                              <input type="file" name="audio_file" class="form-control" accept="audio/*">
                              @if($passage->audio_url)
                                <small class="text-muted mt-1 d-block">Joriy audio: {{ $passage->audio_url }}</small>
                              @endif
                            </div>
                            <div class="mb-3">
                              <label class="form-label">Yoki to'g'ridan-to'g'ri Audio URL havolasi</label>
                              <input type="text" name="audio_url" class="form-control" value="{{ $passage->audio_url }}" placeholder="https://example.com/audio.mp3">
                            </div>
                          @endif

                          <div class="mb-3">
                            <label class="form-label">Matn mazmuni / Topshiriq matni</label>
                            <textarea name="content" class="form-control" rows="8" placeholder="Matnni shu yerga kiriting...">{{ $passage->content }}</textarea>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="main-btn light-btn btn-sm" data-bs-dismiss="modal">Bekor qilish</button>
                          <button type="submit" class="main-btn primary-btn btn-sm">Saqlash</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>

                <!-- Add Question Modal for this passage -->
                <div class="modal fade" id="addQuestionModal-{{ $passage->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                      <form method="POST" action="{{ route('admin.ielts.questions.store', $passage) }}">
                        @csrf
                        <div class="modal-header">
                          <h5 class="modal-title">Yangi savol qo'shish</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <div class="mb-3">
                            <label class="form-label">Savol turi <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                              @if($section->skill === 'writing')
                                <option value="writing_task" selected>Writing Task (Insho / Matn yozish)</option>
                              @elseif($section->skill === 'speaking')
                                <option value="speaking_task" selected>Speaking Task (Ovozli gapirish)</option>
                              @else
                                <option value="multiple_choice">Multiple Choice (Bir nechta variantli)</option>
                                <option value="true_false_ng">True / False / Not Given</option>
                              @endif
                            </select>
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Savol matni <span class="text-danger">*</span></label>
                            <textarea name="question_text" class="form-control" rows="3" placeholder="Savol matnini kiriting..." required></textarea>
                          </div>
                          @if($section->skill !== 'writing' && $section->skill !== 'speaking')
                            <div class="mb-3">
                              <label class="form-label">Variantlar (Multiple Choice uchun, har biri yangi qatorda)</label>
                              <textarea name="options_raw" class="form-control" rows="4" placeholder="A variant&#10;B variant&#10;C variant&#10;D variant"></textarea>
                              <small class="text-muted">True/False/Not Given uchun variant yozish shart emas, avtomatik qo'shiladi.</small>
                            </div>
                            <div class="row">
                              <div class="col-md-8">
                                <div class="mb-3">
                                  <label class="form-label">To'g'ri javob</label>
                                  <input type="text" name="correct_answer" class="form-control" placeholder="Masalan: True yoki to'g'ri javob matni">
                                </div>
                              </div>
                              <div class="col-md-4">
                                <div class="mb-3">
                                  <label class="form-label">Tartib raqami</label>
                                  <input type="number" name="order" class="form-control" placeholder="Avtomatik">
                                </div>
                              </div>
                            </div>
                          @endif
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="main-btn light-btn btn-sm" data-bs-dismiss="modal">Bekor qilish</button>
                          <button type="submit" class="main-btn primary-btn btn-sm">Qo'shish</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              @endforeach
            @endif

            <!-- Add Passage Modal for this section -->
            <div class="modal fade" id="addPassageModal-{{ $section->id }}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <form method="POST" action="{{ route('admin.ielts.passages.store', $section) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                      <h5 class="modal-title">Yangi Matn / Vazifa qo'shish</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <div class="mb-3">
                        <label class="form-label">Sarlavha (ixtiyoriy)</label>
                        <input type="text" name="title" class="form-control" placeholder="Masalan: Section 1 Reading Text">
                      </div>

                      @if($section->skill === 'listening')
                        <div class="mb-3">
                          <label class="form-label">Audio fayl yuklash (mp3, wav, m4a - max 30MB)</label>
                          <input type="file" name="audio_file" class="form-control" accept="audio/*">
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Yoki to'g'ridan-to'g'ri Audio URL</label>
                          <input type="text" name="audio_url" class="form-control" placeholder="https://example.com/audio.mp3">
                        </div>
                      @endif

                      <div class="mb-3">
                        <label class="form-label">Matn mazmuni / Topshiriq matni</label>
                        <textarea name="content" class="form-control" rows="8" placeholder="Matnni shu yerga nusxalab tashlang..."></textarea>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="main-btn light-btn btn-sm" data-bs-dismiss="modal">Bekor qilish</button>
                      <button type="submit" class="main-btn primary-btn btn-sm">Qo'shish</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

          </div>
        @endforeach
      </div>
    </div>

  </div>
</div>
@endsection
