<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <li class="nav-item {{ request()->is('dashboard') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="mdi mdi-view-dashboard menu-icon"></i>
                <span class="menu-title">Dashboard</span>
            </a>
        </li>

        <!-- Menu Teks Bacaan untuk Teacher -->
        @if (auth()->user()->role === 'teacher')
            <li class="nav-item {{ request()->is('courses') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('courses.index') }}">
                    <i class="mdi mdi-school menu-icon"></i>
                    <span class="menu-title">Course</span>
                </a>
            </li>
        @endif

        <!-- Menu Pembelajaran untuk Student -->
        @if (auth()->user()->role === 'student')
            <li class="nav-item {{ request()->is('courses') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('courses.index') }}">
                    <i class="mdi mdi-school menu-icon"></i>
                    <span class="menu-title">Course</span>
                </a>
            </li>
        @endif
    </ul>
</nav>