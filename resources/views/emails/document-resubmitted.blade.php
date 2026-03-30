<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Resubmitted</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7fa; color: #333333; line-height: 1.6; }
        .email-wrapper { width: 100%; background-color: #f4f7fa; padding: 40px 20px; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); }
        .email-header { background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%); padding: 40px 30px; text-align: center; color: #ffffff; }
        .email-header h1 { margin: 0; font-size: 26px; font-weight: 600; letter-spacing: -0.5px; }
        .email-header p { margin: 10px 0 0 0; font-size: 14px; opacity: 0.9; }
        .email-body { padding: 40px 30px; }
        .greeting { font-size: 18px; font-weight: 600; color: #d97706; margin-bottom: 20px; }
        .message { font-size: 15px; line-height: 1.8; color: #555555; margin-bottom: 25px; }
        .document-details { background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 20px; border-radius: 8px; margin: 25px 0; }
        .detail-row { display: table; width: 100%; padding: 10px 0; border-bottom: 1px solid #fde68a; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { display: table-cell; font-weight: 600; color: #374151; width: 140px; padding-right: 15px; vertical-align: top; }
        .detail-value { display: table-cell; color: #555555; vertical-align: top; }
        .resubmit-notes { background-color: #fff7ed; border-left: 4px solid #ea580c; padding: 20px; border-radius: 8px; margin: 25px 0; }
        .resubmit-notes-title { font-size: 13px; font-weight: 700; color: #9a3412; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 10px; }
        .resubmit-notes-text { font-size: 14px; color: #7c2d12; line-height: 1.7; }
        .attempt-badge { display: inline-block; background-color: #fef3c7; color: #b45309; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; margin-bottom: 20px; border: 1px solid #fde68a; }
        .email-footer { background-color: #f8f9fc; padding: 30px; text-align: center; border-top: 1px solid #e5e7eb; }
        .email-footer p { margin: 8px 0; font-size: 13px; color: #6b7280; line-height: 1.5; }
        @media only screen and (max-width: 600px) {
            .email-wrapper { padding: 20px 10px; }
            .email-header, .email-body, .email-footer { padding: 30px 20px; }
            .email-header h1 { font-size: 22px; }
            .detail-row { display: block; }
            .detail-label { display: block; margin-bottom: 5px; width: 100%; }
            .detail-value { display: block; width: 100%; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">

            <!-- Header -->
            <div class="email-header">
                <h1>Document Resubmitted</h1>
                <p>Pension Services Tracking System</p>
            </div>

            <!-- Body -->
            <div class="email-body">
                <div class="greeting">Hello {{ $notifiable->name }}!</div>

                <div style="text-align: center;">
                    <span class="attempt-badge">↺ {{ $suffix }} Resubmission Attempt</span>
                </div>

                <div class="message">
                    A previously rejected document has been resubmitted and is now pending your review. Please take the necessary action at your earliest convenience.
                </div>

                <!-- Document Details Card -->
                <div class="document-details">
                    <div class="detail-row">
                        <div class="detail-label">Document No:</div>
                        <div class="detail-value"><strong>{{ $document->document_number }}</strong></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Title:</div>
                        <div class="detail-value">{{ $document->title }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Type:</div>
                        <div class="detail-value">{{ $document->document_type }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Sender Unit:</div>
                        <div class="detail-value">{{ $document->senderUnit->name ?? 'Unknown Unit' }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Resubmitted By:</div>
                        <div class="detail-value">{{ $resubmittedBy->name }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Date & Time:</div>
                        <div class="detail-value">{{ now()->format('F j, Y g:i A') }}</div>
                    </div>
                </div>

                <!-- Resubmission Notes (conditional) -->
                @if($document->resubmit_notes)
                <div class="resubmit-notes">
                    <div class="resubmit-notes-title">Resubmission Notes</div>
                    <div class="resubmit-notes-text">{{ $document->resubmit_notes }}</div>
                </div>
                @endif

                <div class="message" style="margin-bottom: 0;">
                    Please log in to the system to review this document and take appropriate action.
                </div>
            </div>

            <!-- Footer -->
            <div class="email-footer">
                <p><strong>AFPPGMC · Pension Services Tracking System</strong></p>
                <p>This is an automated notification. Please do not reply to this email.</p>
            </div>

        </div>
    </div>
</body>
</html>