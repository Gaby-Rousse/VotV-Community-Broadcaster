import {useEffect, useState} from 'react';
import type {FormEvent} from 'react';
import {Link, useLocation, useSearchParams} from 'react-router-dom';
import {isAxiosError} from 'axios';
import api from '../../lib/axios';
import {useAuth} from '../../contexts/AuthContext';
import {isAuthenticated} from '../../models/interfaces/User';
import Roles from '../../models/enums/Roles';
import Notifications from '../../components/Notifications';

type ThreadType = 'suggestion' | 'bug';
type Status = 'open' | 'in_progress' | 'resolved' | 'closed';
type AccessStatus = 'pending' | 'approved' | 'refused';
type Author = {id: number; username: string};
type CurrentAccessRequest = {status: AccessStatus; message: string; response_message: string | null};
type Thread = {
    id: number;
    subject: string;
    type: ThreadType;
    status: Status;
    author: Author;
    updated_at: string;
    unread: boolean;
    can_reply: boolean;
    access_request: CurrentAccessRequest | null;
    approval_status: AccessStatus;
    approval_message: string | null;
};
type Message = {id: number; author: Author; content: string; kind: 'reply' | 'status'; created_at: string};
type Conversation = {
    thread: Thread;
    messages: Message[];
    has_older: boolean;
    can_reply: boolean;
    approval_status: AccessStatus;
    approval_message: string | null;
};
type Listing = {data: Thread[]; total: number; last_page: number};
type SubmissionRequest = {id: number; type: ThreadType; subject: string; created_at: string; author: Author};
type PendingAccessRequest = {id: number; message: string; created_at: string; thread: {id: number; type: ThreadType; subject: string}; user: Author};
type RequestQueue = {submissions: SubmissionRequest[]; access_requests: PendingAccessRequest[]};

const statusNames: Record<Status, string> = {
    open: 'Open', in_progress: 'In progress', resolved: 'Resolved', closed: 'Closed',
};

function errorMessage(error: unknown): string {
    return isAxiosError(error) ? error.response?.data?.message ?? 'Unable to reach the server. Please try again.' : 'Something went wrong.';
}

function dateLabel(value: string): string {
    return new Date(value).toLocaleString([], {month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'});
}

export default function Support() {
    const {user, loading: authLoading} = useAuth();
    const {pathname} = useLocation();
    const [params, setParams] = useSearchParams();
    const [listing, setListing] = useState<Listing>({data: [], total: 0, last_page: 1});
    const [conversation, setConversation] = useState<Conversation | null>(null);
    const [listLoading, setListLoading] = useState(true);
    const [threadLoading, setThreadLoading] = useState(false);
    const [error, setError] = useState('');
    const [actionError, setActionError] = useState('');
    const [sending, setSending] = useState(false);
    const [reply, setReply] = useState('');
    const [revision, setRevision] = useState(0);
    const authenticated = isAuthenticated(user);
    const developer = user.role === Roles.Developer;
    const tab = pathname === '/suggestions' ? 'suggestions' : pathname === '/bugs' ? 'bugs' : params.get('view') === 'write' ? 'write' : params.get('view') === 'requests' ? 'requests' : 'mine';
    const threadId = Number(params.get('thread')) || null;
    const page = Math.max(1, Number(params.get('page')) || 1);
    const publicFolder = tab === 'suggestions' || tab === 'bugs';
    const selectedThread = listing.data.find(thread => thread.id === threadId) ?? null;

    useEffect(() => {
        if (authLoading) return;
        if (tab === 'requests') {
            setListing({data: [], total: 0, last_page: 1});
            setListLoading(false);
            return;
        }
        if (!authenticated && !publicFolder) {
            setListing({data: [], total: 0, last_page: 1});
            setListLoading(false);
            return;
        }
        const controller = new AbortController();
        setListLoading(true);
        setError('');
        const refresh = async () => {
            try {
                const response = await api.get<Listing>('/api/v1/support/threads', {
                    signal: controller.signal,
                    params: {page, type: tab === 'suggestions' ? 'suggestion' : tab === 'bugs' ? 'bug' : undefined, mine: tab === 'mine' || tab === 'write' ? 1 : 0},
                });
                setListing(response.data);
                setError('');
            } catch (error) {
                if (!controller.signal.aborted) setError(errorMessage(error));
            } finally {
                if (!controller.signal.aborted) setListLoading(false);
            }
        };
        void refresh();
        const timer = setInterval(refresh, 15000);
        return () => { controller.abort(); clearInterval(timer); };
    }, [authenticated, authLoading, user.id, tab, page, publicFolder, revision]);

    useEffect(() => {
        setConversation(null);
        setReply('');
        setActionError('');
        if (!threadId || tab === 'write' || tab === 'requests' || listLoading || !selectedThread) return;
        const controller = new AbortController();
        setThreadLoading(true);
        const refresh = async () => {
            try {
                const {data} = await api.get<Conversation>(`/api/v1/support/threads/${threadId}`, {signal: controller.signal});
                setConversation(current => {
                    if (!current || current.thread.id !== threadId) return data;
                    const firstId = data.messages[0]?.id ?? 0;
                    return {...data, messages: [...current.messages.filter(message => message.id < firstId), ...data.messages], has_older: current.has_older};
                });
                const lastId = data.messages.at(-1)?.id;
                if (authenticated && data.can_reply && lastId) {
                    await api.post(`/api/v1/support/threads/${threadId}/read`, {message_id: lastId}, {signal: controller.signal});
                    setListing(current => ({...current, data: current.data.map(thread => thread.id === threadId ? {...thread, unread: false} : thread)}));
                }
            } catch (error) {
                if (!controller.signal.aborted) setActionError(errorMessage(error));
            } finally {
                if (!controller.signal.aborted) setThreadLoading(false);
            }
        };
        void refresh();
        const timer = setInterval(refresh, 15000);
        return () => { controller.abort(); clearInterval(timer); };
    }, [authenticated, user.id, threadId, tab, listLoading, selectedThread?.id, revision]);

    async function sendReply(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        setSending(true);
        setActionError('');
        try {
            await api.post(`/api/v1/support/threads/${threadId}/messages`, {content: reply});
            setReply('');
            setRevision(current => current + 1);
        } catch (error) {
            setActionError(errorMessage(error));
        } finally { setSending(false); }
    }

    async function changeStatus(status: Status) {
        setSending(true);
        setActionError('');
        try {
            await api.patch(`/api/v1/support/threads/${threadId}`, {status});
            setRevision(current => current + 1);
        } catch (error) { setActionError(errorMessage(error)); }
        finally { setSending(false); }
    }

    async function loadOlder() {
        if (!conversation) return;
        const selectedId = conversation.thread.id;
        setSending(true);
        try {
            const {data} = await api.get<Conversation>(`/api/v1/support/threads/${selectedId}`, {params: {before: conversation.messages[0].id}});
            setConversation(current => current?.thread.id === selectedId ? {...current, messages: [...data.messages, ...current.messages], has_older: data.has_older} : current);
        } catch (error) { setActionError(errorMessage(error)); }
        finally { setSending(false); }
    }

    function selectThread(id: number) {
        setParams({thread: String(id), page: String(page)});
    }

    return <main className="support">
        <nav className="tabs" aria-label="Support folders">
            <Link to="/" className="home-link">Home</Link>
            <Link to="/suggestions" aria-current={tab === 'suggestions' ? 'page' : undefined}>{developer ? 'All suggestions' : 'Suggestions'}</Link>
            <Link to="/bugs" aria-current={tab === 'bugs' ? 'page' : undefined}>{developer ? 'All bugs' : 'Bugs'}</Link>
            <Link to="/support" aria-current={tab === 'mine' ? 'page' : undefined}>My submissions</Link>
            <Link to="/support?view=write" aria-current={tab === 'write' ? 'page' : undefined}>Write</Link>
            {developer && <Link to="/support?view=requests" aria-current={tab === 'requests' ? 'page' : undefined}>Requests</Link>}
            <div className="account-actions">
                {authenticated && <Notifications className="support-notifications"/>}
                <Link to={authenticated ? '/account' : '/login'} className="account-link">{authenticated ? user.username : 'Login'}</Link>
            </div>
        </nav>
        {tab === 'requests' ? developer ? <DeveloperRequests/> : <section className="requests-panel"><p className="notice">This area is only available to developers.</p></section> : <div className="inbox">
            <section className="mailbox" aria-label="Submissions">
                <header className="mailbox-heading"><h1>Contact support</h1><span>{listing.total} {listing.total === 1 ? 'thread' : 'threads'}</span></header>
                <div className="folder-heading">{tab === 'bugs' ? 'Bug reports' : tab === 'suggestions' ? 'Suggestions' : 'My submissions'}</div>
                {authLoading ? <p className="notice">Connecting...</p> : !authenticated && !publicFolder ? <p className="notice">Log in to view your submissions.</p> : <>
                    {publicFolder
                        ? <p className="privacy">Everyone can read submissions and their messages. Replying requires permission from a developer.</p>
                        : !developer && <p className="privacy">Everyone can read your conversation. Only you, developers, and approved participants can reply.</p>}
                    {error && <p className="error" role="alert">{error} <button onClick={() => setRevision(current => current + 1)}>Retry</button></p>}
                    {listLoading && <p className="notice" role="status">Loading...</p>}
                    {!listLoading && !error && listing.data.length === 0 && <p className="notice">No submissions found.</p>}
                    <div className="thread-list">
                        {listing.data.map(thread => <button key={thread.id} className={`thread-row ${thread.id === threadId ? 'active' : ''}`} onClick={() => selectThread(thread.id)} aria-pressed={thread.id === threadId}>
                            <span className="read-indicator" aria-label={thread.unread ? 'Unread' : 'Read'}>{thread.unread ? '●' : '·'}</span>
                            <span className="sender">{thread.author.username}</span>
                            <span className="subject">{thread.subject}<small>{statusNames[thread.status]}{thread.approval_status !== 'approved' ? ` · Approval ${thread.approval_status}` : !thread.can_reply ? thread.access_request ? ` · Access ${thread.access_request.status}` : ' · Read only' : ''}</small></span>
                            <time dateTime={thread.updated_at}>{dateLabel(thread.updated_at)}</time>
                        </button>)}
                    </div>
                    {listing.last_page > 1 && <footer className="pagination">
                        <button disabled={page === 1} onClick={() => setParams({page: String(page - 1)})}>Previous</button>
                        <span>{page} / {listing.last_page}</span>
                        <button disabled={page >= listing.last_page} onClick={() => setParams({page: String(page + 1)})}>Next</button>
                    </footer>}
                </>}
            </section>
            <section className="reader" aria-label="Conversation" aria-busy={threadLoading}>
                {tab === 'write' && authenticated ? <Compose onCreated={id => { setParams({thread: String(id)}); setRevision(current => current + 1); }}/>
                    : conversation ? <>
                        <header className="message-heading"><span className="avatar" aria-hidden="true">{conversation.thread.author.username.slice(0, 1).toUpperCase()}</span><span>{conversation.thread.author.username}</span><time dateTime={conversation.thread.updated_at}>{dateLabel(conversation.thread.updated_at)}</time></header>
                        <div className="topic"><h2>{conversation.thread.subject}</h2><span>{conversation.thread.type === 'bug' ? 'Bug report' : 'Suggestion'} · {statusNames[conversation.thread.status]}</span></div>
                        {developer && conversation.approval_status === 'approved' && <label className="status-control">Status <select value={conversation.thread.status} disabled={sending} onChange={event => changeStatus(event.target.value as Status)}>{Object.entries(statusNames).map(([value, label]) => <option value={value} key={value}>{label}</option>)}</select></label>}
                        <div className="messages">
                            {conversation.has_older && <button disabled={sending} onClick={loadOlder}>Load earlier messages</button>}
                            {conversation.messages.map(message => <article key={message.id} className={message.kind === 'status' ? 'status-message' : 'message'}>
                                <header><span>{message.author.username}</span><time dateTime={message.created_at}>{dateLabel(message.created_at)}</time></header>
                                <p>{message.content}</p>
                            </article>)}
                        </div>
                        {actionError && <p className="error" role="alert">{actionError}</p>}
                        {conversation.approval_status !== 'approved' ? <SubmissionReviewState conversation={conversation} developer={developer} onReviewed={() => setRevision(current => current + 1)}/>
                            : conversation.thread.status === 'closed' ? <p className="notice">This thread is closed.</p> : conversation.can_reply ? <form className="reply" onSubmit={sendReply}>
                            <label htmlFor="reply-content">Reply</label>
                            <textarea id="reply-content" value={reply} onChange={event => setReply(event.target.value)} maxLength={10000} required disabled={sending}/>
                            <button disabled={sending || !reply.trim()}>{sending ? 'Sending...' : 'Send reply'}</button>
                        </form> : selectedThread && <JoinConversation thread={selectedThread} authenticated={authenticated} onRequested={() => setRevision(current => current + 1)}/>}
                    </> : <>
                        <header className="message-heading"><span className="avatar" aria-hidden="true">@</span><span>Support inbox</span></header>
                        <div className="topic"><h2>{tab === 'write' ? 'Write a submission' : 'Select a conversation'}</h2></div>
                        <div className="empty-reader">
                            <p>{authLoading ? 'Connecting...' : threadLoading ? 'Loading conversation...' : !authenticated ? 'Choose a public submission on the left, or log in to create one.' : 'Choose a submission on the left, or use Write to start one.'}</p>
                            {!authLoading && !authenticated && <Link to="/login">Log in</Link>}
                            {actionError && <p className="error" role="alert">{actionError}</p>}
                        </div>
                    </>}
            </section>
        </div>}
        <footer className="inbox-footer"><span>VCB / SUPPORT</span><span>{authenticated ? 'New replies appear automatically' : 'Not connected'}</span></footer>
    </main>;
}

function Compose({onCreated}: {onCreated: (id: number) => void}) {
    const [type, setType] = useState<ThreadType>('suggestion');
    const [subject, setSubject] = useState('');
    const [content, setContent] = useState('');
    const [error, setError] = useState('');
    const [sending, setSending] = useState(false);

    async function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        setSending(true);
        setError('');
        try {
            const {data} = await api.post<Thread>('/api/v1/support/threads', {type, subject, content});
            onCreated(data.id);
        } catch (error) { setError(errorMessage(error)); }
        finally { setSending(false); }
    }

    return <>
        <header className="message-heading"><span className="avatar" aria-hidden="true">+</span><span>New submission request</span></header>
        <form className="compose" onSubmit={submit}>
            <label htmlFor="submission-type">To: developers / Type</label>
            <select id="submission-type" value={type} onChange={event => setType(event.target.value as ThreadType)} disabled={sending}>
                <option value="suggestion">Suggestion</option><option value="bug">Bug report</option>
            </select>
            <label htmlFor="submission-subject">Subject</label>
            <input id="submission-subject" value={subject} onChange={event => setSubject(event.target.value)} maxLength={200} required disabled={sending}/>
            <label htmlFor="submission-content">Message</label>
            <textarea id="submission-content" value={content} onChange={event => setContent(event.target.value)} maxLength={10000} required disabled={sending}/>
            <p className="privacy">A developer must approve this submission before it becomes public. Approval or refusal includes a response message.</p>
            {error && <p className="error" role="alert">{error}</p>}
            <button disabled={sending || !subject.trim() || !content.trim()}>{sending ? 'Sending...' : 'Request publication'}</button>
        </form>
    </>;
}

function DeveloperRequests() {
    const [queue, setQueue] = useState<RequestQueue>({submissions: [], access_requests: []});
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [revision, setRevision] = useState(0);

    useEffect(() => {
        const controller = new AbortController();
        const refresh = async () => {
            try {
                const {data} = await api.get<RequestQueue>('/api/v1/support/threads/requests/pending', {signal: controller.signal});
                setQueue(data);
                setError('');
            } catch (error) {
                if (!controller.signal.aborted) setError(errorMessage(error));
            } finally {
                if (!controller.signal.aborted) setLoading(false);
            }
        };
        void refresh();
        const timer = setInterval(refresh, 15000);
        return () => { controller.abort(); clearInterval(timer); };
    }, [revision]);

    return <section className="requests-panel">
        <header className="requests-heading"><h1>Developer requests</h1><span>{queue.submissions.length + queue.access_requests.length} pending</span></header>
        {loading && <p className="notice">Loading requests...</p>}
        {error && <p className="error" role="alert">{error}</p>}
        <div className="request-groups">
            <section>
                <h2>Submission approvals</h2>
                {!loading && queue.submissions.length === 0 && <p className="notice">No pending submissions.</p>}
                {queue.submissions.map(submission => <article className="request-card" key={submission.id}>
                    <header><Link to={`/${submission.type === 'bug' ? 'bugs' : 'suggestions'}?thread=${submission.id}`}>{submission.subject}</Link><time dateTime={submission.created_at}>{dateLabel(submission.created_at)}</time></header>
                    <p>{submission.author.username} wants to publish a {submission.type === 'bug' ? 'bug report' : 'suggestion'}.</p>
                    <DecisionForm onDecide={async (decision, message) => {
                        await api.patch(`/api/v1/support/threads/${submission.id}/submission-review`, {decision, message});
                        setRevision(current => current + 1);
                    }}/>
                </article>)}
            </section>
            <section>
                <h2>Conversation access</h2>
                {!loading && queue.access_requests.length === 0 && <p className="notice">No pending access requests.</p>}
                {queue.access_requests.map(request => <article className="request-card" key={request.id}>
                    <header><Link to={`/${request.thread.type === 'bug' ? 'bugs' : 'suggestions'}?thread=${request.thread.id}`}>{request.thread.subject}</Link><time dateTime={request.created_at}>{dateLabel(request.created_at)}</time></header>
                    <p><span>{request.user.username}</span>: {request.message}</p>
                    <DecisionForm onDecide={async (decision, message) => {
                        await api.patch(`/api/v1/support/threads/${request.thread.id}/access-requests/${request.id}`, {decision, message});
                        setRevision(current => current + 1);
                    }}/>
                </article>)}
            </section>
        </div>
    </section>;
}

function SubmissionReviewState({conversation, developer, onReviewed}: {conversation: Conversation; developer: boolean; onReviewed: () => void}) {
    return <section className="submission-review">
        <h3>Submission {conversation.approval_status}</h3>
        {conversation.approval_status === 'pending' ? <>
            <p>This submission is waiting for a developer before it becomes public.</p>
            {developer && <DecisionForm onDecide={async (decision, message) => {
                await api.patch(`/api/v1/support/threads/${conversation.thread.id}/submission-review`, {decision, message});
                onReviewed();
            }}/>} 
        </> : <>
            <p>A developer refused this publication request.</p>
            <blockquote>{conversation.approval_message}</blockquote>
            <Link to="/support?view=write">Create a new submission request</Link>
        </>}
    </section>;
}

function DecisionForm({onDecide}: {onDecide: (decision: 'approved' | 'refused', message: string) => Promise<void>}) {
    const [message, setMessage] = useState('');
    const [error, setError] = useState('');
    const [sending, setSending] = useState(false);

    async function decide(decision: 'approved' | 'refused') {
        setSending(true);
        setError('');
        try {
            await onDecide(decision, message);
        } catch (error) { setError(errorMessage(error)); }
        finally { setSending(false); }
    }

    return <div className="decision-form">
        <label>Response message</label>
        <textarea value={message} onChange={event => setMessage(event.target.value)} maxLength={10000} required disabled={sending}/>
        {error && <p className="error" role="alert">{error}</p>}
        <div>
            <button type="button" disabled={sending || !message.trim()} onClick={() => decide('approved')}>Approve</button>
            <button type="button" disabled={sending || !message.trim()} onClick={() => decide('refused')}>Refuse</button>
        </div>
    </div>;
}

function JoinConversation({thread, authenticated, onRequested}: {thread: Thread; authenticated: boolean; onRequested: () => void}) {
    const [message, setMessage] = useState('');
    const [error, setError] = useState('');
    const [sending, setSending] = useState(false);

    useEffect(() => {
        setMessage('');
        setError('');
    }, [thread.id]);

    async function requestAccess(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        setSending(true);
        setError('');
        try {
            await api.post(`/api/v1/support/threads/${thread.id}/access-requests`, {message});
            setMessage('');
            onRequested();
        } catch (error) { setError(errorMessage(error)); }
        finally { setSending(false); }
    }

    const pending = thread.access_request?.status === 'pending';
    const refused = thread.access_request?.status === 'refused';

    return <div className="access-panel">
            <h3>Join conversation</h3>
            <p>You can read every message. A developer must approve you before you can reply.</p>
            {!authenticated ? <><p>Log in to request permission from the developers.</p><Link to="/login">Log in</Link></>
                : pending ? <>
                    <p className="access-state">Your request is waiting for a developer.</p>
                    <blockquote>{thread.access_request?.message}</blockquote>
                </> : <>
                    {refused && <div className="access-response">
                        <p className="access-state">Your previous request was refused.</p>
                        <blockquote>{thread.access_request?.response_message}</blockquote>
                    </div>}
                    <form onSubmit={requestAccess}>
                        <label htmlFor={`access-message-${thread.id}`}>{refused ? 'Send a new request' : 'Why do you want to join?'}</label>
                        <textarea id={`access-message-${thread.id}`} value={message} onChange={event => setMessage(event.target.value)} maxLength={10000} required disabled={sending}/>
                        {error && <p className="error" role="alert">{error}</p>}
                        <button disabled={sending || !message.trim()}>{sending ? 'Sending...' : 'Request access'}</button>
                    </form>
                </>}
        </div>;
}
