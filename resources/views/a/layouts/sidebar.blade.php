@php
    $isDosen = isDosen();
    $dosenId = dosenId();

    $groupNotifQ=DB::table('groups')->where('send','=','Y')->where('admin_verify','=','N');
    $doneNotifQ=DB::table('groups')->where('groups.done','Y')->where('groups.admin_verify','Y')->where('groups.done_verify','N');
    $laporanNotifQ=DB::table('groups')->where('groups.laporan','Y')->where('groups.admin_verify','Y')->where('groups.laporan_verify','N')->where('done','Y');

    // Dosen hanya melihat notifikasi kelompok bimbingannya
    if($isDosen){
        $groupNotifQ=$groupNotifQ->where('pembimbing_id',$dosenId);
        $doneNotifQ=$doneNotifQ->where('groups.pembimbing_id',$dosenId);
        $laporanNotifQ=$laporanNotifQ->where('groups.pembimbing_id',$dosenId);
    }

    $groupNotif=$groupNotifQ->count();
    $doneNotif=$doneNotifQ->count();
    $laporanNotif=$laporanNotifQ->count();
@endphp
<div id="sidebar">
    <div class="sidebar-wrapper active">
        <div class="sidebar-header position-relative">
            <div class="d-flex justify-content-between align-items-center">
                <div class="logo">
                    <a href="#"><img src="{{asset('app/assets/compiled/svg/logo.svg')}}" alt="Logo" srcset=""></a>
                </div>
                {{-- <div class="theme-toggle d-flex gap-2  align-items-center mt-2">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true"
                        role="img" class="iconify iconify--system-uicons" width="20" height="20"
                        preserveAspectRatio="xMidYMid meet" viewBox="0 0 21 21">
                        <g fill="none" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path
                                d="M10.5 14.5c2.219 0 4-1.763 4-3.982a4.003 4.003 0 0 0-4-4.018c-2.219 0-4 1.781-4 4c0 2.219 1.781 4 4 4zM4.136 4.136L5.55 5.55m9.9 9.9l1.414 1.414M1.5 10.5h2m14 0h2M4.135 16.863L5.55 15.45m9.899-9.9l1.414-1.415M10.5 19.5v-2m0-14v-2"
                                opacity=".3"></path>
                            <g transform="translate(-210 -1)">
                                <path d="M220.5 2.5v2m6.5.5l-1.5 1.5"></path>
                                <circle cx="220.5" cy="11.5" r="4"></circle>
                                <path d="m214 5l1.5 1.5m5 14v-2m6.5-.5l-1.5-1.5M214 18l1.5-1.5m-4-5h2m14 0h2"></path>
                            </g>
                        </g>
                    </svg>
                    <div class="form-check form-switch fs-6">
                        <input class="form-check-input  me-0" type="checkbox" id="toggle-dark" style="cursor: pointer">
                        <label class="form-check-label"></label>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true"
                        role="img" class="iconify iconify--mdi" width="20" height="20" preserveAspectRatio="xMidYMid meet"
                        viewBox="0 0 24 24">
                        <path fill="currentColor"
                            d="m17.75 4.09l-2.53 1.94l.91 3.06l-2.63-1.81l-2.63 1.81l.91-3.06l-2.53-1.94L12.44 4l1.06-3l1.06 3l3.19.09m3.5 6.91l-1.64 1.25l.59 1.98l-1.7-1.17l-1.7 1.17l.59-1.98L15.75 11l2.06-.05L18.5 9l.69 1.95l2.06.05m-2.28 4.95c.83-.08 1.72 1.1 1.19 1.85c-.32.45-.66.87-1.08 1.27C15.17 23 8.84 23 4.94 19.07c-3.91-3.9-3.91-10.24 0-14.14c.4-.4.82-.76 1.27-1.08c.75-.53 1.93.36 1.85 1.19c-.27 2.86.69 5.83 2.89 8.02a9.96 9.96 0 0 0 8.02 2.89m-1.64 2.02a12.08 12.08 0 0 1-7.8-3.47c-2.17-2.19-3.33-5-3.49-7.82c-2.81 3.14-2.7 7.96.31 10.98c3.02 3.01 7.84 3.12 10.98.31Z">
                        </path>
                    </svg>
                </div> --}}
                <div class="sidebar-toggler  x">
                    <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                </div>
            </div>
        </div>
        <div class="sidebar-menu">
            <ul class="menu">
                
                <li class="sidebar-item {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                    <a href="{{route('dashboard')}}" class='sidebar-link'>
                        <i class="bi bi-grid-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-item {{ request()->is('group','group/*') ? 'active' : '' }}">
                    <a href="{{route('group.index')}}" class='sidebar-link'>
                        <i class="bi bi-people"></i>
                        <span>Pengelompokan</span>
                        @if ($groupNotif>0)
                            <span class="badge bg-danger rounded-pill">{{$groupNotif}}</span>
                        @endif
                    </a>
                </li>

                @can('pembekalan-list')
                <li class="sidebar-item {{ request()->is('pembekalan','pembekalan/*') ? 'active' : '' }}">
                    <a href="{{route('pembekalan.index')}}" class='sidebar-link'>
                        <i class="bi bi-list-check"></i>
                        <span>Pembekalan</span>
                    </a>
                </li>
                @endcan

                @if($isDosen || Gate::allows('verifikasi pi-list'))
                <li class="sidebar-item {{ request()->is('verifikasi-pi','verifikasi-pi/*') ? 'active' : '' }}">
                    <a href="{{route('verifikasi-pi.index')}}" class='sidebar-link'>
                        <i class="bi bi-card-checklist"></i>
                        <span>Verifikasi Selesai Pi</span>
                        @if ($doneNotif>0)
                            <span class="badge bg-danger rounded-pill">{{$doneNotif}}</span>
                        @endif
                    </a>
                </li>
                @endif
                @if($isDosen || Gate::allows('laporan nilai-list'))
                <li class="sidebar-item {{ request()->is('laporan-nilai','laporan-nilai/*') ? 'active' : '' }}">
                    <a href="{{route('laporan-nilai.index')}}" class='sidebar-link'>
                        <i class="bi bi-flag-fill"></i>
                        <span>Laporan & Upload Nilai</span>
                        @if ($laporanNotif>0)
                            <span class="badge bg-danger rounded-pill">{{$laporanNotif}}</span>
                        @endif
                    </a>
                </li>
                @endif
                @if(Session::get('role')=='superadmin' || Session::get('role')=='admin')
                <li class="sidebar-item  has-sub">
                    <a href="#" class='sidebar-link'>
                        <i class="bi bi-stack"></i>
                        <span>Master</span>
                    </a>
                    <ul class="submenu ">
                        
                        @can('mahasiswa-list')
                        <li class="submenu-item">
                            <a href="{{route('mahasiswa.index')}}" class="submenu-link">Mahasiswa</a>
                        </li>
                        @endcan
                        @can('dosen-list')
                        <li class="submenu-item">
                            <a href="{{route('dosen.index')}}" class="submenu-link">Dosen</a>
                            
                        </li>
                        @endcan

                        @can('lokasi pi-list')
                        <li class="submenu-item">
                            <a href="{{route('lokasi-pi.index')}}" class="submenu-link">Lokasi PI</a>
                            
                        </li>
                        @endcan
                        @can('user-list')
                        <li class="submenu-item">
                            <a href="{{route('users.index')}}" class="submenu-link">Admin</a>
                        </li>
                        @endcan
                        
                    </ul>
                </li>
                @endif
                @role('superadmin')
                <li class="sidebar-item {{ request()->is('roles','roles/*') ? 'active' : '' }}">
                    <a href="{{route('roles.index')}}" class='sidebar-link'>
                        <i class="bi bi-gear"></i>
                        <span>Role</span>
                    </a>
                </li>   
                <li class="sidebar-item {{ request()->is('permissions','permissions/*') ? 'active' : '' }}">
                    <a href="{{route('permissions.index')}}" class='sidebar-link'>
                        <i class="bi bi-gear"></i>
                        <span>Permission</span>
                    </a>
                </li>
                @endrole
                
                
                {{-- <li class="sidebar-item {{ request()->is('setting','setting/*') ? 'active' : '' }}">
                    <a href="{{route('setting.index')}}" class='sidebar-link'>
                        <i class="bi bi-gear"></i>
                        <span>Pengaturan</span>
                    </a>
                </li> --}}
                <li class="sidebar-item {{ request()->is('account') ? 'active' : '' }}">
                    <a href="{{route('account')}}" class='sidebar-link'>
                        <i class="bi bi-person-bounding-box"></i>
                        <span>My Account</span>
                    </a>
                </li>
                <li class="sidebar-item ">
                    <a href="{{route('login.logout')}}" class='sidebar-link text-danger'>
                        <i class="bi bi-box-arrow-left text-danger"></i>
                        <span>Logout</span>
                    </a>
                </li>
                
            </ul>
        </div>
    </div>
</div>