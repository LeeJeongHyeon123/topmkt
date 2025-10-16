<?php
/**
 * 탑마케팅 개인정보처리방침 페이지
 */
$page_title = '개인정보처리방침';
$page_description = '탑마케팅 서비스 개인정보처리방침을 확인하세요';
$current_page = 'privacy';

require_once SRC_PATH . '/views/templates/header.php';
?>

<!-- 개인정보처리방침 페이지 -->
<section class="legal-section">
    <div class="container">
        <div class="legal-header">
            <h1 class="legal-title">개인정보처리방침</h1>
            <p class="legal-subtitle">탑마케팅(TopMKT) 개인정보처리방침</p>
            <div class="legal-meta">
                <span class="effective-date">시행일자: 2024년 1월 1일</span>
                <span class="version">버전: 1.0</span>
            </div>
        </div>

        <div class="legal-content">
            <article class="privacy-article">
                <div class="privacy-intro">
                    <p>탑마케팅(이하 "회사")은 개인정보보호법 제30조에 따라 정보주체의 개인정보를 보호하고 이와 관련한 고충을 신속하고 원활하게 처리할 수 있도록 하기 위하여 다음과 같이 개인정보 처리방침을 수립·공개합니다.</p>
                </div>

                <section class="privacy-section">
                    <h2>제1조 (개인정보의 처리목적)</h2>
                    <p>회사는 다음의 목적을 위하여 개인정보를 처리합니다. 처리하고 있는 개인정보는 다음의 목적 이외의 용도로는 이용되지 않으며, 이용 목적이 변경되는 경우에는 개인정보보호법 제18조에 따라 별도의 동의를 받는 등 필요한 조치를 이행할 예정입니다.</p>
                    
                    <div class="purpose-list">
                        <h3>1. 회원 가입 및 관리</h3>
                        <ul>
                            <li>회원 식별, 본인확인, 연령확인</li>
                            <li>불량회원의 부정 이용 방지</li>
                            <li>비밀번호 변경 및 분실 시 본인확인</li>
                            <li>회원탈퇴 의사 확인</li>
                            <li>고지사항 전달</li>
                        </ul>

                        <h3>2. 서비스 제공</h3>
                        <ul>
                            <li>네트워크 마케팅 커뮤니티 서비스 제공</li>
                            <li>콘텐츠 제공, 맞춤형 서비스 제공</li>
                            <li>본인인증, 연령인증</li>
                            <li>요금결제, 정산</li>
                            <li>물품배송, 청구서 발송</li>
                        </ul>

                        <h3>3. 고객상담 및 민원처리</h3>
                        <ul>
                            <li>민원인의 신원 확인</li>
                            <li>민원사항 확인, 사실조사를 위한 연락·통지</li>
                            <li>처리결과 통보</li>
                        </ul>

                        <h3>4. 마케팅 및 광고에의 활용</h3>
                        <ul>
                            <li>신규 서비스 개발 및 맞춤 서비스 제공</li>
                            <li>이벤트 및 광고성 정보 제공 및 참여기회 제공</li>
                            <li>인구통계학적 특성에 따른 서비스 제공 및 광고 게재</li>
                            <li>서비스의 유효성 확인, 접속빈도 파악 또는 회원의 서비스 이용에 대한 통계</li>
                        </ul>
                    </div>
                </section>

                <section class="privacy-section">
                    <h2>제2조 (개인정보의 처리 및 보유기간)</h2>
                    <ol>
                        <li>회사는 법령에 따른 개인정보 보유·이용기간 또는 정보주체로부터 개인정보를 수집 시에 동의받은 개인정보 보유·이용기간 내에서 개인정보를 처리·보유합니다.</li>
                        <li>각각의 개인정보 처리 및 보유 기간은 다음과 같습니다:
                            <div class="retention-table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>구분</th>
                                            <th>보유항목</th>
                                            <th>보유기간</th>
                                            <th>근거</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>회원정보</td>
                                            <td>이름, 휴대폰번호, 이메일, 주소</td>
                                            <td>회원탈퇴 시까지</td>
                                            <td>서비스 이용약관</td>
                                        </tr>
                                        <tr>
                                            <td>접속로그</td>
                                            <td>접속 일시, IP주소</td>
                                            <td>3개월</td>
                                            <td>통신비밀보호법</td>
                                        </tr>
                                        <tr>
                                            <td>결제정보</td>
                                            <td>결제기록, 거래내역</td>
                                            <td>5년</td>
                                            <td>전자상거래법</td>
                                        </tr>
                                        <tr>
                                            <td>민원처리</td>
                                            <td>문의내용, 처리결과</td>
                                            <td>3년</td>
                                            <td>전자상거래법</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </li>
                    </ol>
                </section>

                <section class="privacy-section">
                    <h2>제3조 (처리하는 개인정보의 항목)</h2>
                    <p>회사는 다음의 개인정보 항목을 처리하고 있습니다:</p>
                    
                    <div class="data-category">
                        <h3>1. 필수수집항목</h3>
                        <ul>
                            <li><strong>회원가입 시:</strong> 이름, 휴대폰번호, 이메일주소, 비밀번호</li>
                            <li><strong>서비스 이용 시:</strong> 접속 IP정보, 쿠키, 접속로그, 서비스 이용기록</li>
                        </ul>

                        <h3>2. 선택수집항목</h3>
                        <ul>
                            <li>주소, 생년월일, 성별</li>
                            <li>프로필 사진, 관심분야</li>
                            <li>마케팅 정보 수신 동의</li>
                        </ul>

                        <h3>3. 자동 수집항목</h3>
                        <ul>
                            <li>IP 주소, 접속 시간, 서비스 이용 기록</li>
                            <li>브라우저 종류 및 버전, 운영체제</li>
                            <li>쿠키, 접속 로그, 방문 페이지</li>
                        </ul>
                    </div>
                </section>

                <section class="privacy-section">
                    <h2>제4조 (개인정보의 제3자 제공)</h2>
                    <ol>
                        <li>회사는 정보주체의 개인정보를 제1조(개인정보의 처리목적)에서 명시한 범위 내에서만 처리하며, 정보주체의 동의, 법률의 특별한 규정 등 개인정보보호법 제17조에 해당하는 경우에만 개인정보를 제3자에게 제공합니다.</li>
                        <li>회사는 다음과 같이 개인정보를 제3자에게 제공하고 있습니다:
                            <div class="third-party-table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>제공받는 자</th>
                                            <th>제공목적</th>
                                            <th>제공항목</th>
                                            <th>보유기간</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>알리고(SMS 발송업체)</td>
                                            <td>인증번호 및 알림 SMS 발송</td>
                                            <td>휴대폰번호</td>
                                            <td>발송 완료 후 즉시 삭제</td>
                                        </tr>
                                        <tr>
                                            <td>Google(reCAPTCHA)</td>
                                            <td>스팸 및 악성 행위 방지</td>
                                            <td>IP주소, 브라우저 정보</td>
                                            <td>Google 개인정보처리방침에 따름</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </li>
                    </ol>
                </section>

                <section class="privacy-section">
                    <h2>제5조 (개인정보처리의 위탁)</h2>
                    <ol>
                        <li>회사는 원활한 개인정보 업무처리를 위하여 다음과 같이 개인정보 처리업무를 위탁하고 있습니다:</li>
                        <li>
                            <div class="delegation-table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>위탁받는 자</th>
                                            <th>위탁업무</th>
                                            <th>개인정보 보유기간</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>웹호스팅 업체</td>
                                            <td>시스템 운영 및 관리</td>
                                            <td>위탁계약 종료 시까지</td>
                                        </tr>
                                        <tr>
                                            <td>SMS 발송업체</td>
                                            <td>인증번호 및 알림 발송</td>
                                            <td>발송 완료 후 즉시 삭제</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </li>
                        <li>회사는 위탁계약 체결시 개인정보보호법 제26조에 따라 위탁업무 수행목적 외 개인정보 처리금지, 기술적·관리적 보호조치, 재위탁 제한, 수탁자에 대한 관리·감독, 손해배상 등 책임에 관한 사항을 계약서 등 문서에 명시하고, 수탁자가 개인정보를 안전하게 처리하는지를 감독하고 있습니다.</li>
                    </ol>
                </section>

                <section class="privacy-section">
                    <h2>제6조 (정보주체의 권리·의무 및 행사방법)</h2>
                    <ol>
                        <li>정보주체는 회사에 대해 언제든지 다음 각 호의 개인정보 보호 관련 권리를 행사할 수 있습니다:
                            <ul>
                                <li>개인정보 처리현황 통지요구</li>
                                <li>개인정보 열람요구</li>
                                <li>개인정보 정정·삭제요구</li>
                                <li>개인정보 처리정지요구</li>
                            </ul>
                        </li>
                        <li>제1항에 따른 권리 행사는 회사에 대해 개인정보보호법 시행령 제41조제1항에 따라 서면, 전자우편, 모사전송(FAX) 등을 통하여 하실 수 있으며 회사는 이에 대해 지체없이 조치하겠습니다.</li>
                        <li>정보주체가 개인정보의 오류 등에 대한 정정 또는 삭제를 요구한 경우에는 회사는 정정 또는 삭제를 완료할 때까지 당해 개인정보를 이용하거나 제공하지 않습니다.</li>
                        <li>제1항에 따른 권리 행사는 정보주체의 법정대리인이나 위임을 받은 자 등 대리인을 통하여 하실 수 있습니다. 이 경우 개인정보보호법 시행규칙 별지 제11호 서식에 따른 위임장을 제출하셔야 합니다.</li>
                    </ol>
                </section>

                <section class="privacy-section">
                    <h2>제7조 (개인정보의 파기)</h2>
                    <ol>
                        <li>회사는 개인정보 보유기간의 경과, 처리목적 달성 등 개인정보가 불필요하게 되었을 때에는 지체없이 해당 개인정보를 파기합니다.</li>
                        <li>정보주체로부터 동의받은 개인정보 보유기간이 경과하거나 처리목적이 달성되었음에도 불구하고 다른 법령에 따라 개인정보를 계속 보존하여야 하는 경우에는, 해당 개인정보를 별도의 데이터베이스(DB)로 옮기거나 보관장소를 달리하여 보존합니다.</li>
                        <li>개인정보 파기의 절차 및 방법은 다음과 같습니다:
                            <div class="destruction-method">
                                <h4>파기절차</h4>
                                <p>회사는 파기 사유가 발생한 개인정보를 선정하고, 회사의 개인정보 보호책임자의 승인을 받아 개인정보를 파기합니다.</p>
                                
                                <h4>파기방법</h4>
                                <ul>
                                    <li><strong>전자적 형태:</strong> 기록을 재생할 수 없도록 로우레벨포맷(Low Level Format) 등의 방법을 이용하여 파기</li>
                                    <li><strong>종이 문서:</strong> 분쇄기로 분쇄하거나 소각하여 파기</li>
                                </ul>
                            </div>
                        </li>
                    </ol>
                </section>

                <section class="privacy-section">
                    <h2>제8조 (개인정보의 안전성 확보조치)</h2>
                    <p>회사는 개인정보보호법 제29조에 따라 다음과 같이 안전성 확보에 필요한 기술적/관리적 및 물리적 조치를 하고 있습니다:</p>
                    
                    <div class="security-measures">
                        <h3>1. 개인정보 취급직원의 최소화 및 교육</h3>
                        <p>개인정보를 취급하는 직원을 지정하고 담당자에 한정시켜 최소화하여 개인정보를 관리하는 대책을 시행하고 있습니다.</p>

                        <h3>2. 정기적인 자체 감사 실시</h3>
                        <p>개인정보 취급 관련 안정성 확보를 위해 정기적(분기 1회)으로 자체 감사를 실시하고 있습니다.</p>

                        <h3>3. 내부관리계획의 수립 및 시행</h3>
                        <p>개인정보의 안전한 처리를 위하여 내부관리계획을 수립하고 시행하고 있습니다.</p>

                        <h3>4. 개인정보의 암호화</h3>
                        <p>이용자의 개인정보는 비밀번호는 암호화되어 저장 및 관리되고 있어, 본인만이 알 수 있으며 중요한 데이터는 파일 및 전송 데이터를 암호화하거나 파일 잠금 기능을 사용하는 등의 별도 보안기능을 사용하고 있습니다.</p>

                        <h3>5. 해킹 등에 대비한 기술적 대책</h3>
                        <p>회사는 해킹이나 컴퓨터 바이러스 등에 의한 개인정보 유출 및 훼손을 막기 위하여 보안프로그램을 설치하고 주기적인 갱신·점검을 하며 외부로부터 접근이 통제된 구역에 시스템을 설치하고 기술적/물리적으로 감시 및 차단하고 있습니다.</p>

                        <h3>6. 개인정보에 대한 접근 제한</h3>
                        <p>개인정보를 처리하는 데이터베이스시스템에 대한 접근권한의 부여,변경,말소를 통하여 개인정보에 대한 접근통제를 위하여 필요한 조치를 하고 있으며 침입차단시스템을 이용하여 외부로부터의 무단 접근을 통제하고 있습니다.</p>

                        <h3>7. 접속기록의 보관 및 위변조 방지</h3>
                        <p>개인정보처리시스템에 접속한 기록을 최소 1년 이상 보관, 관리하고 있으며, 접속 기록이 위변조 및 도난, 분실되지 않도록 보안기능을 사용하고 있습니다.</p>

                        <h3>8. 문서보안을 위한 잠금장치 사용</h3>
                        <p>개인정보가 포함된 서류, 보조저장매체 등을 잠금장치가 있는 안전한 장소에 보관하고 있습니다.</p>
                        
                        <h3>9. 웹사이트 성능 최적화 기술</h3>
                        <p>회사는 사용자 경험 향상을 위해 다음과 같은 기술적 조치를 시행하고 있습니다:</p>
                        <ul>
                            <li><strong>레이지 로딩(Lazy Loading):</strong> 이미지 및 콘텐츠를 사용자가 실제로 보는 시점에 로드하여 페이지 성능을 향상시킵니다.</li>
                            <li><strong>브라우저 캐싱:</strong> 정적 리소스를 사용자 브라우저에 일시적으로 저장하여 재방문 시 빠른 로딩을 제공합니다.</li>
                            <li><strong>데이터 압축:</strong> 전송되는 데이터를 압축하여 네트워크 사용량을 최소화합니다.</li>
                            <li><strong>개인정보 보호:</strong> 위 기술들은 개인정보를 수집하거나 추적하지 않으며, 순수히 성능 향상 목적으로만 사용됩니다.</li>
                        </ul>
                    </div>
                </section>

                <section class="privacy-section">
                    <h2>제9조 (개인정보 보호책임자)</h2>
                    <p>회사는 개인정보 처리에 관한 업무를 총괄해서 책임지고, 개인정보 처리와 관련한 정보주체의 불만처리 및 피해구제 등을 위하여 아래와 같이 개인정보 보호책임자를 지정하고 있습니다:</p>
                    
                    <div class="contact-info">
                        <div class="contact-card">
                            <h3>개인정보 보호책임자</h3>
                            <ul>
                                <li><strong>성명:</strong> 김정보</li>
                                <li><strong>직책:</strong> 개인정보보호팀장</li>
                                <li><strong>연락처:</strong> 02-1234-5678</li>
                                <li><strong>이메일:</strong> privacy@topmktx.com</li>
                            </ul>
                        </div>

                        <div class="contact-card">
                            <h3>개인정보 보호담당부서</h3>
                            <ul>
                                <li><strong>부서명:</strong> 개인정보보호팀</li>
                                <li><strong>담당자:</strong> 이개인</li>
                                <li><strong>연락처:</strong> 02-1234-5679</li>
                                <li><strong>이메일:</strong> privacy@topmktx.com</li>
                            </ul>
                        </div>
                    </div>

                    <p>정보주체께서는 회사의 서비스(또는 사업)을 이용하시면서 발생한 모든 개인정보 보호 관련 문의, 불만처리, 피해구제 등에 관한 사항을 개인정보 보호책임자 및 담당부서로 문의하실 수 있습니다. 회사는 정보주체의 문의에 대해 지체없이 답변 및 처리해드릴 것입니다.</p>
                </section>

                <section class="privacy-section">
                    <h2>제10조 (권익침해 구제방법)</h2>
                    <p>정보주체는 아래의 기관에 대해 개인정보 침해신고, 상담등을 문의하실 수 있습니다. 아래의 기관은 회사와는 별개의 기관으로서, 회사의 자체적인 개인정보 불만처리, 피해구제 결과에 만족하지 못하시거나 보다 자세한 도움이 필요하시면 문의하여 주시기 바랍니다.</p>
                    
                    <div class="remedy-contacts">
                        <div class="remedy-item">
                            <h3>▶ 개인정보 침해신고센터 (개인정보보호위원회 운영)</h3>
                            <ul>
                                <li>소관업무: 개인정보 침해신고, 상담, 집단분쟁조정</li>
                                <li>홈페이지: privacy.go.kr</li>
                                <li>전화: (국번없이) 182</li>
                                <li>주소: (01811) 서울특별시 종로구 세종대로 209 정부서울청사 4층</li>
                            </ul>
                        </div>

                        <div class="remedy-item">
                            <h3>▶ 개인정보 분쟁조정위원회</h3>
                            <ul>
                                <li>소관업무: 개인정보 분쟁조정신청, 집단분쟁조정 (민사적 해결)</li>
                                <li>홈페이지: www.kopico.go.kr</li>
                                <li>전화: (국번없이) 1833-6972</li>
                                <li>주소: (03171)서울특별시 종로구 세종대로 209 정부서울청사 4층</li>
                            </ul>
                        </div>

                        <div class="remedy-item">
                            <h3>▶ 대검찰청 사이버범죄수사단</h3>
                            <ul>
                                <li>홈페이지: www.spo.go.kr</li>
                                <li>전화: 02-3480-3573</li>
                            </ul>
                        </div>

                        <div class="remedy-item">
                            <h3>▶ 경찰청 사이버테러대응센터</h3>
                            <ul>
                                <li>홈페이지: www.netan.go.kr</li>
                                <li>전화: (국번없이) 182</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section class="privacy-section">
                    <h2>제11조 (개인정보 처리방침 변경)</h2>
                    <ol>
                        <li>이 개인정보처리방침은 시행일로부터 적용되며, 법령 및 방침에 따른 변경내용의 추가, 삭제 및 정정이 있는 경우에는 변경사항의 시행 7일 전부터 공지사항을 통하여 고지할 것입니다.</li>
                        <li>본 방침은 정부의 정책 또는 보안기술의 변화에 따라 내용의 추가, 삭제 및 수정이 있을 시에는 개정 최소 7일 전에 홈페이지를 통해 변경이유 및 내용 등을 공지하도록 하겠습니다.</li>
                    </ol>
                </section>

                <div class="privacy-footer">
                    <p class="effective-notice">
                        <strong>부칙:</strong><br>
                        ① (시행일) 이 방침은 2024년 1월 1일부터 시행됩니다.<br>
                        ② 이전의 개인정보처리방침은 아래에서 확인하실 수 있습니다.
                    </p>
                    
                    <div class="version-history">
                        <h3>개정 이력</h3>
                        <ul>
                            <li>2024.01.01 - 최초 제정</li>
                        </ul>
                    </div>
                </div>
            </article>
        </div>
    </div>
</section>

<style>
.legal-section {
    min-height: 100vh;
    padding: 2rem 0;
    background: linear-gradient(135deg, #f8f9ff 0%, #e8f4fd 100%);
}

.legal-header {
    text-align: center;
    margin-bottom: 3rem;
    padding: 2rem;
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.legal-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.legal-subtitle {
    font-size: 1.2rem;
    color: #7f8c8d;
    margin-bottom: 1rem;
}

.legal-meta {
    display: flex;
    justify-content: center;
    gap: 2rem;
    font-size: 0.9rem;
    color: #95a5a6;
}

.legal-content {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    overflow: hidden;
}

.privacy-article {
    padding: 3rem;
    line-height: 1.8;
}

.privacy-intro {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 10px;
    margin-bottom: 2rem;
    font-size: 1.1rem;
}

.privacy-section {
    margin-bottom: 3rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #ecf0f1;
}

.privacy-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.privacy-section h2 {
    color: #2c3e50;
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    padding-left: 1rem;
    border-left: 4px solid #3498db;
}

.privacy-section h3 {
    color: #34495e;
    font-size: 1.2rem;
    font-weight: 600;
    margin: 1.5rem 0 1rem 0;
}

.privacy-section h4 {
    color: #34495e;
    font-size: 1.1rem;
    font-weight: 600;
    margin: 1rem 0 0.5rem 0;
}

.purpose-list ul,
.data-category ul {
    margin-left: 1rem;
    margin-bottom: 1rem;
}

.purpose-list li,
.data-category li {
    margin-bottom: 0.3rem;
    color: #555;
}

.retention-table table,
.third-party-table table,
.delegation-table table {
    width: 100%;
    border-collapse: collapse;
    margin: 1rem 0;
    background: #f8f9fa;
    border-radius: 8px;
    overflow: hidden;
}

.retention-table th,
.third-party-table th,
.delegation-table th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
}

.retention-table td,
.third-party-table td,
.delegation-table td {
    padding: 0.8rem 1rem;
    border-bottom: 1px solid #dee2e6;
    color: #555;
}

.destruction-method {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    margin: 1rem 0;
}

.security-measures {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 10px;
    margin: 1rem 0;
}

.security-measures h3 {
    color: #2c3e50;
    margin-top: 1.5rem;
    margin-bottom: 0.5rem;
}

.security-measures p {
    color: #555;
    margin-bottom: 1rem;
}

.contact-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin: 2rem 0;
}

.contact-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.contact-card h3 {
    color: white;
    margin-bottom: 1rem;
    font-size: 1.2rem;
}

.contact-card ul {
    list-style: none;
    padding: 0;
}

.contact-card li {
    margin-bottom: 0.5rem;
    color: #f1f2f6;
}

.remedy-contacts {
    margin: 2rem 0;
}

.remedy-item {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    border-left: 4px solid #3498db;
}

.remedy-item h3 {
    color: #2c3e50;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.remedy-item ul {
    list-style: none;
    padding: 0;
}

.remedy-item li {
    margin-bottom: 0.3rem;
    color: #555;
}

.privacy-footer {
    background: #34495e;
    color: white;
    padding: 2rem;
    border-radius: 10px;
    margin-top: 2rem;
}

.effective-notice {
    font-size: 1rem;
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

.version-history h3 {
    color: #ecf0f1;
    margin-bottom: 1rem;
}

.version-history ul {
    list-style: none;
    padding: 0;
}

.version-history li {
    color: #bdc3c7;
    margin-bottom: 0.3rem;
}

/* 반응형 디자인 */
@media (max-width: 768px) {
    .legal-title {
        font-size: 2rem;
    }
    
    .legal-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .privacy-article {
        padding: 1.5rem;
    }
    
    .contact-info {
        grid-template-columns: 1fr;
    }
    
    .retention-table table,
    .third-party-table table,
    .delegation-table table {
        font-size: 0.9rem;
    }
    
    .retention-table th,
    .third-party-table th,
    .delegation-table th,
    .retention-table td,
    .third-party-table td,
    .delegation-table td {
        padding: 0.5rem;
    }
}

/* 인쇄 스타일 */
@media print {
    .legal-section {
        background: white;
        padding: 0;
    }
    
    .legal-header,
    .legal-content {
        box-shadow: none;
        border: 1px solid #ddd;
    }
    
    .contact-card {
        background: white;
        color: black;
        border: 1px solid #ddd;
    }
    
    .contact-card h3 {
        color: black;
    }
}
</style>

<?php require_once SRC_PATH . '/views/templates/footer.php'; ?> 