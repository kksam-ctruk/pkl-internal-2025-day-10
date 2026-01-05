<?php $__env->startSection('title', 'Laporan Penjualan'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800">Laporan Penjualan</h2>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row align-items-end g-3">
                <div class="col-md-3">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="date_from" value="<?php echo e($dateFrom); ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="<?php echo e($dateTo); ?>" class="form-control">
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                    
                    <a href="<?php echo e(route('admin.reports.export-sales', request()->all())); ?>" class="btn btn-success">
                        <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                    </a>
                </div>
            </form>
        </div>
    </div>

    
    <div class="row g-4 mb-4">
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-4 border-success">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-bold">Total Pendapatan</div>
                    <div class="h3 fw-bold text-dark mb-0">
                         Rp <?php echo e(number_format($summary->total_revenue ?? 0, 0, ',', '.')); ?>

                    </div>
                    <small class="text-muted">Periode ini</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-4 border-primary">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-bold">Total Transaksi</div>
                    <div class="h3 fw-bold text-dark mb-0">
                        <?php echo e(number_format($summary->total_orders ?? 0)); ?>

                    </div>
                    <small class="text-muted">Order paid</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Performa Kategori</h5>
                </div>
                <div class="card-body">
                    <?php $__currentLoopData = $byCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-medium"><?php echo e($cat->name); ?></span>
                                <span class="fw-bold">Rp <?php echo e(number_format($cat->total, 0, ',', '.')); ?></span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" role="progressbar"
                                     style="width: <?php echo e(($cat->total / ($summary->total_revenue ?: 1)) * 100); ?>%">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                     <h5 class="card-title mb-0">Rincian Transaksi</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Order ID</th>
                                <th>Tanggal</th>
                                <th>Customer</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo e(route('admin.orders.show', $order)); ?>" class="fw-bold text-decoration-none">
                                            #<?php echo e($order->order_number); ?>

                                        </a>
                                    </td>
                                    <td><?php echo e($order->created_at->format('d M Y H:i')); ?></td>
                                    <td>
                                        <div class="fw-bold"><?php echo e($order->user->name); ?></div>
                                        <div class="small text-muted"><?php echo e($order->user->email); ?></div>
                                    </td>
                                    <td class="text-end fw-bold">
                                        Rp <?php echo e(number_format($order->total_amount, 0, ',', '.')); ?>

                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        Tidak ada data penjualan pada periode ini.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white">
                    <?php echo e($orders->appends(request()->all())->links()); ?>

                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/haitsam/Documents/pkl-2025/resources/views/admin/reports/sales.blade.php ENDPATH**/ ?>