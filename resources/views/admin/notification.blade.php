@extends('admin.layouts.main')

@section('title', 'Notifications | Admin Panel')

@section('content')
<section class="table-components">
        <div class="container-fluid">
          <div class="title-wrapper pt-30">
            <div class="row align-items-center">
              <div class="col-md-6">
                <div class="title">
                  <h2>Notifications</h2>
                </div>
              </div>
              <div class="col-md-6">
                <div class="breadcrumb-wrapper">
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item active" aria-current="page">
                        Notifications
                      </li>
                    </ol>
                  </nav>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-lg-12">
              <div class="card-style mb-30">
                <h6 class="mb-10">So'nggi bildirishnomalar</h6>
                <p class="text-sm mb-20">Hozircha yangi bildirishnoma mavjud emas.</p>
              </div>
            </div>
          </div>
        </div>
      </section>
@endsection

