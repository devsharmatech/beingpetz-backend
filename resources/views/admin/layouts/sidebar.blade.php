<!-- ========== Left Sidebar Start ========== -->
<style>
    .vertical-menu a {
        color: #000;
    }

    .vertical-menu a.active-link {
        color: rgb(131, 55, 178) !important;
        font-weight: 600;
    }

    .vertical-menu li.mm-active>a {
        color: rgb(131, 55, 178) !important;
    }

    .vertical-menu .sub-menu li a.active-link {
        color: rgb(131, 55, 178) !important;
    }
</style>
<div class="vertical-menu" style="background-color: rgb(131, 55, 178);">
    <div data-simplebar class="h-100">
        <div id="sidebar-menu" style="background-color: rgb(131, 55, 178);">
            <ul class="metismenu list-unstyled" id="side-menu">

                @php
                    use Illuminate\Support\Facades\Auth;

                    $user = Auth::user();

                    function checkPermission($permission, $user)
                    {
                        if (!$user) {
                            return false;
                        }

                        
                        if ($user->role_id) {
                            $role = \App\Models\Role::find($user->role_id);

                            if ($role && $role->name === 'admin') {
                                return true;
                            }
                        }

                        
                        $permissions = $role->default_permissions ?? [];

                        if (is_string($permissions)) {
                            $permissions = json_decode($permissions, true) ?? [];
                        }

                        if (!empty($permissions)) {
                            return in_array($permission, $permissions) || in_array('*', $permissions);
                        }

                       
                        if ($user->role_id) {
                            $role = \App\Models\Role::find($user->role_id);

                            if ($role) {
                                $rolePermissions = $role->default_permissions;

                                if (is_string($rolePermissions)) {
                                    $rolePermissions = json_decode($rolePermissions, true) ?? [];
                                }

                                return in_array($permission, $rolePermissions) || in_array('*', $rolePermissions);
                            }
                        }

                       
                        return false;
                    }
                @endphp

                <!-- Dashboard - Always visible -->
                <li class="{{ request()->routeIs('admin.dashboard') ? 'mm-active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}"
                        class="waves-effect {{ request()->routeIs('admin.dashboard') ? 'active-link' : '' }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- Category -->
                @if (checkPermission('categories', $user))
                    <li class="{{ request()->routeIs('admin.categories.*') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.categories.index') }}"
                            class="waves-effect {{ request()->routeIs('admin.categories.*') ? 'active-link' : '' }}">
                            <i class="fas fa-list-alt"></i>
                            <span>Category</span>
                        </a>
                    </li>
                @endif

                <!-- Blogs -->
                @if (checkPermission('blogs', $user))
                    <li class="{{ request()->routeIs('admin.blogs.*') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.blogs.index') }}"
                            class="waves-effect {{ request()->routeIs('admin.blogs.*') ? 'active-link' : '' }}">
                            <i class="fas fa-blog"></i>
                            <span>Blogs</span>
                        </a>
                    </li>
                @endif

                <!-- Events -->
                @if (checkPermission('events', $user))
                    <li class="{{ request()->routeIs('admin.events.*') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.events.list') }}"
                            class="waves-effect {{ request()->routeIs('admin.events.*') ? 'active-link' : '' }}">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Events</span>
                        </a>
                    </li>
                @endif

                <!-- Pets -->
                @if (checkPermission('pets', $user))
                    <li class="{{ request()->routeIs('admin.pets.*') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.pets.list') }}"
                            class="waves-effect {{ request()->routeIs('admin.pets.*') ? 'active-link' : '' }}">
                            <i class="fas fa-paw"></i>
                            <span>Pets</span>
                        </a>
                    </li>
                @endif

                <!-- Parents -->
                @if (checkPermission('parents', $user))
                    <li class="{{ request()->routeIs('admin.parents.*') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.parents.index') }}"
                            class="waves-effect {{ request()->routeIs('admin.parents.*') ? 'active-link' : '' }}">
                            <i class="fas fa-user-friends"></i>
                            <span>Parents</span>
                        </a>
                    </li>
                @endif

                <!-- Users & Vendors -->
                @if (checkPermission('uservendors', $user))
                    <li class="{{ request()->routeIs('admin.uservendors.*') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.uservendors.index') }}"
                            class="waves-effect {{ request()->routeIs('admin.uservendors.*') ? 'active-link' : '' }}">
                            <i class="fas fa-user-friends"></i>
                            <span>Users & Vendors</span>
                        </a>
                    </li>
                @endif
                <!-- Product Category -->
                @if (checkPermission('product-category', $user))
                    <li class="{{ request()->routeIs('admin-page.categories.page') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin-page.categories.page') }}"
                            class="waves-effect {{ request()->routeIs('admin-page.categories.page') ? 'active-link' : '' }}">
                            <i class="fas fa-box"></i>
                            <span>Product Category</span>
                        </a>
                    </li>
                @endif
                <!-- Company Page -->
                @if (checkPermission('companies-page', $user))
                    <li class="{{ request()->routeIs('admin-page.companies.page') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin-page.companies.page') }}"
                            class="waves-effect {{ request()->routeIs('admin-page.companies.page') ? 'active-link' : '' }}">
                            <i class="fas fa-box"></i>
                            <span>Company Manager</span>
                        </a>
                    </li>
                @endif
                <!-- Attributes -->
                @if (checkPermission('attributes-page', $user))
                    <li class="{{ request()->routeIs('admin-page.attributes.page') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin-page.attributes.page') }}"
                            class="waves-effect {{ request()->routeIs('admin-page.attributes.page') ? 'active-link' : '' }}">
                            <i class="fas fa-box"></i>
                            <span>Product Attributes</span>
                        </a>
                    </li>
                @endif
                <!-- Products -->
                @if (checkPermission('products-page', $user))
                    <li class="{{ request()->routeIs('admin-page.products.page') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin-page.products.page') }}"
                            class="waves-effect {{ request()->routeIs('admin-page.products.page') ? 'active-link' : '' }}">
                            <i class="fas fa-box"></i>
                            <span>Products</span>
                        </a>
                    </li>
                @endif
                <!-- Products -->
                @if (checkPermission('products-order', $user))
                    <li class="{{ request()->routeIs('admin-page.orders.page') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin-page.orders.page') }}"
                            class="waves-effect {{ request()->routeIs('admin-page.orders.page') ? 'active-link' : '' }}">
                            <i class="fas fa-box"></i>
                            <span>Orders</span>
                        </a>
                    </li>
                @endif
                <!-- Market Place Banner -->
                @if (checkPermission('banners-page', $user))
                    <li class="{{ request()->routeIs('admin-page.banners.page') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin-page.banners.page') }}"
                            class="waves-effect {{ request()->routeIs('admin-page.banners.page') ? 'active-link' : '' }}">
                            <i class="fas fa-box"></i>
                            <span>Marketplace Banners</span>
                        </a>
                    </li>
                @endif
                <!-- Contest Page -->
                @if (checkPermission('contests-page', $user))
                    <li class="{{ request()->routeIs('admin-page.contests.page') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin-page.contests.page') }}"
                            class="waves-effect {{ request()->routeIs('admin-page.contests.page') ? 'active-link' : '' }}">
                            <i class="fas fa-box"></i>
                            <span>Contests</span>
                        </a>
                    </li>
                @endif
                <!-- Contest Entry -->
                @if (checkPermission('entries-page', $user))
                    <li class="{{ request()->routeIs('admin-page.entries.page') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin-page.entries.page') }}"
                            class="waves-effect {{ request()->routeIs('admin-page.entries.page') ? 'active-link' : '' }}">
                            <i class="fas fa-box"></i>
                            <span>Contest Entries</span>
                        </a>
                    </li>
                @endif

                <!-- Community -->
                @if (checkPermission('community', $user))
                    <li class="{{ request()->routeIs('admin.community.*') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.community.index') }}"
                            class="waves-effect {{ request()->routeIs('admin.community.*') ? 'active-link' : '' }}">
                            <i class="fas fa-users"></i>
                            <span>Community</span>
                        </a>
                    </li>
                @endif

                <!-- Services -->
                @if (checkPermission('services', $user))
                    <li class="{{ request()->routeIs('admin.services.*') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.services.index') }}"
                            class="waves-effect {{ request()->routeIs('admin.services.*') ? 'active-link' : '' }}">
                            <i class="fas fa-concierge-bell"></i>
                            <span>Services</span>
                        </a>
                    </li>
                @endif

                <!-- Banner -->
                @if (checkPermission('banner', $user))
                    <li class="{{ request()->routeIs('admin.banner.*') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.banner.index') }}"
                            class="waves-effect {{ request()->routeIs('admin.banner.*') ? 'active-link' : '' }}">
                            <i class="fas fa-images"></i>
                            <span>Banner</span>
                        </a>
                    </li>
                @endif

                <!-- Service Banner -->
                @if (checkPermission('service-banner', $user))
                    <li class="{{ request()->routeIs('admin.service-banner.*') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.service-banner.index') }}"
                            class="waves-effect {{ request()->routeIs('admin.service-banner.*') ? 'active-link' : '' }}">
                            <i class="fas fa-ad"></i>
                            <span>Service Banner</span>
                        </a>
                    </li>
                @endif

                <!-- Post Manager -->
                @if (checkPermission('post', $user))
                    <li class="{{ request()->routeIs('admin.post.*') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.post.index') }}"
                            class="waves-effect {{ request()->routeIs('admin.post.*') ? 'active-link' : '' }}">
                            <i class="fas fa-newspaper"></i>
                            <span>Post Manager</span>
                        </a>
                    </li>
                @endif

                <!-- Reported Manager -->
                @if (checkPermission('reports', $user))
                    <li class="{{ request()->routeIs('admin.reports.*') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.reports.index') }}"
                            class="waves-effect {{ request()->routeIs('admin.reports.*') ? 'active-link' : '' }}">
                            <i class="fas fa-flag"></i>
                            <span>Reported Manager</span>
                        </a>
                    </li>
                @endif

                <!-- Messages Manager -->
                <!-- @if (checkPermission('messages', $user))
                    <li class="{{ request()->routeIs('admin.messages.*') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.messages.index') }}"
                            class="waves-effect {{ request()->routeIs('admin.messages.*') ? 'active-link' : '' }}">
                            <i class="fas fa-comments"></i>
                            <span>Messages Manager</span>
                        </a>
                    </li>
                @endif -->

                <!-- Push Notification -->
                @if (checkPermission('notifications', $user))
                    <li class="{{ request()->routeIs('admin.notifications.*') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.notifications.index') }}"
                            class="waves-effect {{ request()->routeIs('admin.notifications.*') ? 'active-link' : '' }}">
                            <i class="fas fa-bell"></i>
                            <span>Push Notification</span>
                        </a>
                    </li>
                @endif

                <!-- Roles & Permissions - Without Dropdown -->
                @if (checkPermission('roles.view', $user) || checkPermission('permissions.view', $user))
                    <!-- Roles -->
                    @if (checkPermission('roles.view', $user))
                        <li class="{{ request()->routeIs('admin.roles.*') ? 'mm-active' : '' }}">
                            <a href="{{ route('admin.roles.index') }}"
                                class="waves-effect {{ request()->routeIs('admin.roles.*') ? 'active-link' : '' }}">
                                <i class="fas fa-user-tag"></i>
                                <span>Roles</span>
                            </a>
                        </li>
                    @endif

                    <!-- Permissions -->
                    @if (checkPermission('permissions.view', $user))
                        <li class="{{ request()->routeIs('admin.permissions.*') ? 'mm-active' : '' }}">
                            <a href="{{ route('admin.permissions.index') }}"
                                class="waves-effect {{ request()->routeIs('admin.permissions.*') ? 'active-link' : '' }}">
                                <i class="fas fa-key"></i>
                                <span>Permissions</span>
                            </a>
                        </li>
                    @endif
                @endif

                <!-- Legal & Compliance -->
                @if (checkPermission('terms-conditions', $user)) 
                    <li class="{{ request()->routeIs('admin.legal.*') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.legal.index') }}"
                            class="waves-effect {{ request()->routeIs('admin.legal.*') ? 'active-link' : '' }}">
                            <i class="fas fa-file-contract"></i>
                            <span>Terms & Conditions</span>
                        </a>
                    </li>
                @endif

                <!-- Settings -->
                @if (checkPermission('settings', $user))
                    <li class="{{ request()->routeIs('admin.settings.*') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.settings.index') }}"
                            class="waves-effect {{ request()->routeIs('admin.settings.*') ? 'active-link' : '' }}">
                            <i class="fas fa-cogs"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                @endif



            </ul>
        </div>
    </div>
</div>
<!-- Left Sidebar End -->
